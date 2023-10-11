<?php

namespace App\Controller;

use App\Form\DeleteAuthorType;
use App\Helper\Connector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/authors', name: 'authors')]
class AuthorController extends AbstractController
{
    public function __construct(
        private Connector $client,
    ) {}

    #[Route('/', methods: ['GET'], name: '_list')]
    public function list(Request $request): Response
    {
        $query = $request->query;
        if (!$query->has('limit')) {
            $query->add(['limit' => 6]);
        }

        $response = $this->client->connect('get', 'authors', $query->all());
        foreach ($response['items'] as &$author) {
            $author = $this->client->connect('get', 'authors/' . $author['id']);

            if (!count($author['books'])) {
                $author['form'] = $this->createForm(DeleteAuthorType::class, [
                    'url' => $this->generateUrl('authors_delete', ['author' => $author['id']])
                ])->createView();
            }
        }
        unset($author);

        return $this->render('author/list.html.twig', $response);
    }

    #[Route('/{author}', methods: ['GET'], name: '_single')]
    public function single(string $author): Response
    {
        $response = $this->client->connect('get', 'authors/' . $author);
        foreach ($response['books'] as &$book) {
            $book['form'] = $this->createForm(DeleteAuthorType::class, [
                'url' => $this->generateUrl('books_delete', ['book' => $book['id']])
            ])->createView();
        }
        unset($book);

        return $this->render('author/single.html.twig', $response);
    }

    #[Route('/{author}', methods: ['DELETE'], name: '_delete')]
    public function delete(string $author): RedirectResponse
    {
        $this->client->connect('delete', 'authors/' . $author);

        return $this->redirectToRoute('authors_list');
    }
}

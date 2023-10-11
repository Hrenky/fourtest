<?php

namespace App\Controller;

use App\Form\CreateBookType;
use App\Helper\Connector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/books', name: 'books')]
class BookController extends AbstractController
{
    public function __construct(
        private Connector $client,
    ) {}

    #[Route('/{book}', methods: ['GET', 'POST'], name: '_single')]
    public function single(Request $request, string $book = ''): Response|RedirectResponse
    {
        $data = [];
        if (!empty($book)) {
            $data = $this->client->connect('get', 'books/' . $book);
        }

        $authors = [];
        $authors_data = $this->client->connect('get', 'authors');
        foreach ($authors_data['items'] as $author) {
            $authors[$author['first_name'] . ' ' . $author['last_name']] = $author['id'];
        }
        $data['authors'] = $authors;

        $form = $this->createForm(CreateBookType::class, options: ['data' => $data]);

        $form->handleRequest($request);
        if (empty($book) && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            unset($data['authors']);
            $data['author'] = ['id' => $data['author']];

            $response = $this->client->connect('post', 'books', data: $data, array: false);

            if ($response->getStatusCode() === JsonResponse::HTTP_OK) {
                return $this->redirectToRoute('authors_list');
            }
        }

        return $this->render('book/single.html.twig', [
            'id' => $book,
            'authors' => $authors,
            'form' => $form
        ]);
    }

    #[Route('/{book}', methods: ['DELETE'], name: '_delete')]
    public function delete(Request $request, string $book): RedirectResponse
    {
        $referer = $request->headers->get('referer');
        $this->client->connect('delete', 'books/' . $book);

        return $this->redirect($referer);
    }
}

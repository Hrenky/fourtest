<?php

namespace App\Controller;

use App\Form\LoginType;
use App\Helper\Connector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

#[Route('/auth', name: 'auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private Connector $client,
        private CacheInterface $cache
    ) {}

    #[Route('/login', name: '_login')]
    public function login(Request $request): Response|RedirectResponse
    {
        $error = '';
        $form = $this->createForm(LoginType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $response = $this->client->getToken(data: $form->getData());

            if ($response->getStatusCode() === JsonResponse::HTTP_OK) {
                return $this->redirectToRoute('authors_list');
            }

            $error = 'Invalid email or password';
        }

        return $this->render('auth/form.html.twig', [
            'form' => $form,
            'error' => $error
        ]);
    }

    #[Route('/logout', methods: ['GET'], name: '_logout')]
    public function logout(): RedirectResponse
    {
        $this->cache->clear();

        return $this->redirectToRoute('auth_login');
    }
}

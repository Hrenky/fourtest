<?php

namespace App\Controller;

use App\Helper\Connector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(
        private Connector $client,
    ) {}

    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        $response = $this->client->connect('get', 'me');

        return $this->render('user/profile.html.twig', $response);
    }
}

<?php

namespace App\EventSubscriber;

use App\Controller\AuthController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\CacheInterface;

class AuthenticationCheckSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CacheInterface $cache,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function onKernelController(ControllerEvent $event): void
    {
        if ($event->getRequestType() === 1) {
            $controller_data = $event->getController();
            $controller = $controller_data[0];
            $method = $controller_data[1];

            if (!$this->cache->hasItem('access_token') && !$controller instanceof AuthController && $method !== 'login') {
                $response = new RedirectResponse($this->urlGenerator->generate('auth_login'));
                $response->send();
            }

            if ($this->cache->hasItem('access_token') && $controller instanceof AuthController && $method === 'login') {
                $response = new RedirectResponse($this->urlGenerator->generate('authors_list'));
                $response->send();
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}

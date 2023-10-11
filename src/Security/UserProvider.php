<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        dump('identify');
        exit;
        // TODO: Implement loadUserByIdentifier() method.
    }

    public function refreshUser(UserInterface $user)
    {
        dump('refresh');
        exit;
        // TODO: Implement refreshUser() method.
    }

    public function supportsClass(string $class)
    {
        dump('support');
        exit;
        // TODO: Implement supportsClass() method.
    }
}
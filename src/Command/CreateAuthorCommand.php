<?php

namespace App\Command;

use App\Helper\Connector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsCommand(
    name: 'app:create-author',
    description: 'Create a new author',
)]
class CreateAuthorCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private Connector $client,
        private CacheInterface $cache,
        private ValidatorInterface $validator
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email in case the user is not logged in')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password in case the user is not logged in')
            ->addArgument('first_name', InputArgument::OPTIONAL, 'First name of author')
            ->addArgument('last_name', InputArgument::OPTIONAL, 'Last name of author')
            ->addArgument('birthday', InputArgument::OPTIONAL, 'Birthday of author')
            ->addArgument('biography', InputArgument::OPTIONAL, 'Biography of author')
            ->addArgument('gender', InputArgument::OPTIONAL, 'Gender of author')
            ->addArgument('place_of_birth', InputArgument::OPTIONAL, 'Place of birth of author')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (! $this->cache->hasItem('access_token')) {
            do {
                $this->io->info('You have to enter login credentials first.');
                $email = $this->io->ask('Email: ');
                $password = $this->io->askHidden('Password: ');

                $response = $this->client->getToken(data: [
                    'email' => $email,
                    'password' => $password
                ]);

                if ($response->getStatusCode() !== JsonResponse::HTTP_OK) {
                    $this->io->error('Invalid credentials');
                }
            } while ($response->getStatusCode() !== JsonResponse::HTTP_OK);
        }

        $first_name = $this->io->ask('First name: ');
        $input->setArgument('first_name', $first_name);

        $last_name = $this->io->ask('Last name: ');
        $input->setArgument('last_name', $last_name);

        do {
            $birthday = $this->io->ask('Enter a birthday date (DD.MM.YYYY): ');
            $valid = $this->validator->validate($birthday, new DateTime('d.m.Y', payload: $birthday));
        } while (count($valid));
        $input->setArgument('birthday', $birthday);

        $biography = $this->io->ask('Biography: ');
        $input->setArgument('biography', $biography);

        $gender = $this->io->choice('Gender: ', ['male', 'female']);
        $input->setArgument('gender', $gender);

        $place_of_birth = $this->io->ask('Place of birth: ');
        $input->setArgument('place_of_birth', $place_of_birth);

        parent::interact($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->client->connect('post', 'authors', data: [
            'first_name' => $input->getArgument('first_name'),
            'last_name' => $input->getArgument('last_name'),
            'birthday' => $input->getArgument('birthday'),
            'biography' => $input->getArgument('biography'),
            'gender' => $input->getArgument('gender'),
            'place_of_birth' => $input->getArgument('place_of_birth')
        ]);

        $this->io->success('You have created a new author');

        return Command::SUCCESS;
    }
}

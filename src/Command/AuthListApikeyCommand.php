<?php

namespace App\Command;

use App\Repository\Main\ApiKeyUserRepository;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:auth:list-apikey',
    description: 'List apikeys',
)]
class AuthListApikeyCommand extends Command
{
    public function __construct(
        private readonly ApiKeyUserRepository $apiKeyUserRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $apiKeys = $this->apiKeyUserRepository->findAll();
        $message = ['Listing apikeys...', '', '<id>. <name>'];

        foreach ($apiKeys as $apiKey) {
            $id = $apiKey->getId();
            $name = $apiKey->getName();

            $message[] = $id.'. '.$name;
        }

        $io->info(implode("\n", $message));

        return Command::SUCCESS;
    }
}

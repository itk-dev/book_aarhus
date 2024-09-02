<?php

namespace App\Command;

use App\Service\ApiKeyUserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:auth:create-apikey',
    description: 'Create an apikey',
)]
class AuthCreateApikeyCommand extends Command
{
    public function __construct(
        private readonly ApiKeyUserService $apiKeyUserService,
    ) {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $io->ask('Enter name for ApiKeyUser');
        $webformApiKey = $io->ask('Enter webform api-key');

        $apiKeyUser = $this->apiKeyUserService->createApiKey($name, $webformApiKey);
        $apiKey = $apiKeyUser->getApiKey();

        $io->writeln('------------');
        $io->writeln('Apikey:');
        $io->writeln('------------');
        $io->writeln("$apiKey");
        $io->writeln('------------');

        $io->success("Created ApiKeyUser $name");

        return Command::SUCCESS;
    }
}

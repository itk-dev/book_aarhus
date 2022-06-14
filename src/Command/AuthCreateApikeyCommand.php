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
    public function __construct(private ApiKeyUserService $apiKeyUserService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $io->ask('Enter name for ApiKeyUser');

        $apiKeyUser = $this->apiKeyUserService->createApiKey($name);

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

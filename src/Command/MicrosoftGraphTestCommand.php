<?php

namespace App\Command;

use App\Service\MicrosoftGraphService;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:microsoft-graph:test',
    description: 'Test requests for Microsoft Graph.',
)]
class MicrosoftGraphTestCommand extends Command
{
    public function __construct(private MicrosoftGraphService $microsoftGraphService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('endpoint', InputArgument::REQUIRED, 'Microsoft graph endpoint to call. For example /me')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $endpoint = $input->getArgument('endpoint');

        $username = $io->ask('Enter username');

        if (null == $username) {
            $io->error('Username is required');

            return Command::INVALID;
        }

        $password = $io->askHidden('Enter password');

        if (null == $password) {
            $io->error('Password is required');

            return Command::INVALID;
        }

        try {
            $accessToken = $this->microsoftGraphService->authenticateAsUser($username, $password);
            $graphResponse = $this->microsoftGraphService->request($endpoint, $accessToken);
            $body = $graphResponse->getBody();

            $io->info(json_encode($body));
        } catch (GuzzleException|GraphException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

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
    name: 'app:graph:test',
    description: 'Test that the application can connect to Microsoft Graph.',
)]
class GraphTestCommand extends Command
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

        try {
            $accessToken = $this->microsoftGraphService->authenticate();
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

<?php

namespace App\Command;

use App\Service\BookingServiceInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:graph:test',
    description: 'Test requests for Microsoft Graph.',
)]
class GraphTestCommand extends Command
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('endpoint', InputArgument::REQUIRED, 'Microsoft graph endpoint to call. For example /me');
        $this->addOption('ask-for-credentials', null, InputOption::VALUE_NONE, 'Set to ask for username/password. Otherwise the service account will be used.');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $endpoint = $input->getArgument('endpoint');

        $askFormCredentials = $input->getOption('ask-for-credentials');
        if ($askFormCredentials) {
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
        }

        try {
            if ($askFormCredentials && !empty($username) && !empty($password)) {
                $token = $this->bookingService->authenticateAsUser($username, $password);

                if (!isset($token['access_token'])) {
                    throw new Exception('Access token not available.');
                }

                $accessToken = $token['access_token'];
            } else {
                $accessToken = $this->bookingService->authenticateAsServiceAccount();
            }

            $graphResponse = $this->bookingService->request($endpoint, $accessToken);
            $body = $graphResponse->getBody();

            $io->info(json_encode($body));
        } catch (GuzzleException|GraphException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

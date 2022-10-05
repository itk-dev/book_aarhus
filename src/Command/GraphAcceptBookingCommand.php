<?php

namespace App\Command;

use App\Service\MicrosoftGraphServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:graph:accept-booking',
    description: 'Accept a booking',
)]
class GraphAcceptBookingCommand extends Command
{
    public function __construct(
        private readonly MicrosoftGraphServiceInterface $microsoftGraphService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Id of booking to accept.');
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('id');

        $io->note(sprintf('Accepting booking with id: %s', $id));

        $data = $this->microsoftGraphService->acceptBooking($id);

        $io->info(json_encode($data));

        return Command::SUCCESS;
    }
}

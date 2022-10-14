<?php

namespace App\Command;

use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;
use App\Service\BookingServiceInterface;
use App\Service\MicrosoftGraphServiceInterface;
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
        private readonly MicrosoftGraphServiceInterface $microsoftGraphService,
        private readonly BookingServiceInterface $bookingService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Id of booking to accept.');
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     * @throws UserBookingException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('id');

        $io->note(sprintf('Accepting booking with id: %s', $id));

        $userBookingData = $this->microsoftGraphService->getBooking($id);

        $userBooking = $this->bookingService->getUserBookingFromGraphData($userBookingData);

        $data = $this->microsoftGraphService->acceptBooking($userBooking);

        $io->info(json_encode($data));

        return Command::SUCCESS;
    }
}

<?php

// @codeCoverageIgnoreStart

namespace App\Command;

use App\Interface\BookingServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test:graph-user-booking',
    description: 'Get user booking for given UserId',
)]
class TestGraphUserBookingsCommand extends Command
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('userId', InputArgument::REQUIRED, 'User id to get bookings for.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userId = $input->getArgument('userId');
        $io->note(sprintf('You request bookings for userId: %s', $userId));

        $data = $this->bookingService->getUserBookings($userId);
        $io->info(json_encode($data));

        return Command::SUCCESS;
    }
}
// @codeCoverageIgnoreEnd

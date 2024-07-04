<?php

// @codeCoverageIgnoreStart

namespace App\Command;

use App\Service\BookingServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test:graph-booking-from-uid',
    description: 'Get user booking from uid.',
)]
class TestGetBookingFromUidCommand extends Command
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('uid', InputArgument::REQUIRED, 'Uid to get booking for.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $uid = $input->getArgument('uid');
        $io->note(sprintf('You request bookings for uid: %s', $uid));

        $data = $this->bookingService->getBookingIdFromIcalUid($uid);
        $io->info(json_encode($data));

        return Command::SUCCESS;
    }
}
// @codeCoverageIgnoreEnd

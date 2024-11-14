<?php

namespace App\Command;

use App\Service\UserBookingCacheServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user-booking-cache:update',
    description: 'Update the user booking cache',
)]
class UserBookingUpdateCacheCommand extends Command
{
    public function __construct(
        private readonly UserBookingCacheServiceInterface $userBookingCacheServiceInterface,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->userBookingCacheServiceInterface->updateCache();
        $io = new SymfonyStyle($input, $output);
        $io->success('Updated user booking cache.');

        return Command::SUCCESS;
    }
}

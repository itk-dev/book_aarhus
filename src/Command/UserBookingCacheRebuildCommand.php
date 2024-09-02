<?php

namespace App\Command;

use App\Service\UserBookingCacheServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user-booking-cache:rebuild',
    description: 'Rebuild the user booking cache',
)]
class UserBookingCacheRebuildCommand extends Command
{
    public function __construct(
        private readonly UserBookingCacheServiceInterface $userBookingCacheServiceInterface,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->userBookingCacheServiceInterface->rebuildCache();
        $io = new SymfonyStyle($input, $output);
        $io->success('Rebuilt user booking cache.');

        return Command::SUCCESS;
    }
}

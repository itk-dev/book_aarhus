<?php

namespace App\Command;

use App\Service\MicrosoftGraphServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:graph:busy',
    description: 'Get busy intervals for resource',
)]
class GraphFreeBusyCommand extends Command
{
    public function __construct(private MicrosoftGraphServiceInterface $microsoftGraphService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('schedules', InputArgument::IS_ARRAY, 'Array of emails to get busy intervals for.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $schedules = $input->getArgument('schedules');

        if (!$schedules) {
            $io->error('Please enter some schedules to get busy intervals for.');

            return Command::INVALID;
        }

        $io->note(sprintf('You request busy intervals for the following schedules: %s', implode(', ', $schedules)));

        $now = new \DateTime();
        $nowPlusOneDay = (new \DateTime())->add(new \DateInterval('P1D'));
        $busyIntervals = $this->microsoftGraphService->getBusyIntervals($schedules, $now, $nowPlusOneDay);

        $io->info(json_encode($busyIntervals));

        return Command::SUCCESS;
    }
}

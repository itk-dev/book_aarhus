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
    name: 'app:graph:free-busy',
    description: 'Get free/busy for schedules',
)]
class GraphFreeBusyCommand extends Command
{
    public function __construct(private MicrosoftGraphService $microsoftGraphService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'schedules',
            InputArgument::IS_ARRAY,
            'Array of schedules to get free/busy for.'
        );
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $schedules = $input->getArgument('schedules');

        if (!$schedules) {
            $io->error('Please enter some schedules to get free/busy for.');

            return Command::INVALID;
        }

        $io->note(sprintf('You request free/busy for the following schedules: %s', implode(', ', $schedules)));

        $now = new \DateTime();
        $nowPlusOneDay = (new \DateTime())->add(new \DateInterval('P1D'));

        $freeBusy = $this->microsoftGraphService->getBusyIntervals($schedules, $now, $nowPlusOneDay);

        $io->info(json_encode($freeBusy));

        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Service\MicrosoftGraphServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:graph:create-booking',
    description: 'Create a booking',
)]
class GraphCreateBookingCommand extends Command
{
    public function __construct(private MicrosoftGraphServiceInterface $microsoftGraphService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $resourceEmail = $io->ask('Resource email');

        if (empty($resourceEmail)) {
            $io->error('Resource email must be set.');

            return Command::FAILURE;
        }

        $resourceName = $io->ask('Resource name');

        if (empty($resourceName)) {
            $io->error('Resource name must be set.');

            return Command::FAILURE;
        }

        $startOffset = $io->ask('Start offset from now (DateInterval, e.g. PT1H (see https://www.php.net/manual/en/dateinterval.construct.php))', 'PT1H');

        $endOffset = $io->ask('End offset from now (DateInterval, e.g. PT1H (see https://www.php.net/manual/en/dateinterval.construct.php))', 'PT2H');

        $subject = $io->ask('Subject');

        if (empty($subject)) {
            $io->error('Subject must be set.');

            return Command::FAILURE;
        }

        $body = $io->ask('body', '');

        $invitation = $io->confirm('Create as invitation?', false);
        $invitationString = $invitation ? 'yes' : 'no';

        $start = (new \DateTime())->add(new \DateInterval($startOffset));
        $end = (new \DateTime())->add(new \DateInterval($endOffset));

        $startString = $start->format('c');
        $endString = $end->format('c');

        $confirmText = [
            'Create booking with the following data?',
            "Resource email: $resourceEmail",
            "Resource name: $resourceName",
            "Start time: $startString",
            "End time: $endString",
            "Subject: $subject",
            "Body: $body",
            "Send as invitation: $invitationString",
            "\n\n",
        ];

        $confirm = $io->confirm(implode("\n", $confirmText));

        if (!$confirm) {
            $io->warning('Aborted');

            return Command::FAILURE;
        }

        if ($invitation) {
            $data = $this->microsoftGraphService->createBookingInviteResource(
                $resourceEmail,
                $resourceName,
                $subject,
                $body,
                $start,
                $end
            );
        } else {
            $data = $this->microsoftGraphService->createBookingForResource(
                $resourceEmail,
                $resourceName,
                $subject,
                $body,
                $start,
                $end
            );
        }

        $io->info(json_encode($data));

        return Command::SUCCESS;
    }
}

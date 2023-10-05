<?php

namespace App\Command;

use App\Service\UserBookingCacheServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user-booking-cache:change',
    description: 'Change a row in the user booking cache table',
)]
class UserBookingCacheChangeCommand extends Command
{
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct
    (
        private readonly UserBookingCacheServiceInterface $userBookingCacheServiceInterface
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $data = [];

        $entityId = $io->ask('Please enter the exchangeId of the entity to change');

        // Whether to change another field after this one.
        $another = true;

        while ($another) {
            // Options are used in the entity set methods, ie setTitle, setUid etc.
            $field = $io->choice(
                'Please select the field to update',
                ['title', 'uid', 'start', 'end', 'status', 'resource'],
                0
            );

            $fieldValue = $io->ask('Please enter the value for '.$field.' field (In case of date fields use format '.UserBookingCacheChangeCommand::DATE_FORMAT.')');

            // Date fields expect Datetime object.
            if ('start' === $field || 'end' === $field) {
                $fieldValue = \DateTime::createFromFormat(UserBookingCacheChangeCommand::DATE_FORMAT, $fieldValue);
            }

            $data[$field] = $fieldValue;

            // Info on current state to be changed.
            $io->writeln('Making the following changes to Cache Entry with id: '.$entityId);
            $io->info(json_encode($data));

            $another = $io->confirm(
                'Change another field for this entity?',
                false
            );
        }

        $write = $io->confirm(
            'Continue? Select yes to write changes to DB, select no to abort.',
            false
        );

        if ($write) {
            $this->userBookingCacheServiceInterface->changeCacheEntry($entityId, $data);
            $io->writeln($entityId.' was changed.');
        } else {
            $io->writeln('Aborted.');
        }

        return Command::SUCCESS;
    }
}

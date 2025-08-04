<?php

// @codeCoverageIgnoreStart

namespace App\Command;

use App\Interface\NotificationServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test:bookingnotification-create-ical',
    description: 'Create example ical file',
)]
class TestBookingNotificationCreateIcalCommand extends Command
{
    public function __construct(private readonly NotificationServiceInterface $notificationService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $events = $this->getEvents();
        $calendarComponent = $this->notificationService->createCalendarComponent($events);

        file_put_contents('resources/calendar.ics', (string) $calendarComponent);

        $io->success('Ics file created. (resources/calendar.ics)');

        return Command::SUCCESS;
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        $json = file_get_contents('public/fixtures/example-ical-event-data.json');
        $events = json_decode($json, true);

        return $events['data'];
    }
}
// @codeCoverageIgnoreEnd

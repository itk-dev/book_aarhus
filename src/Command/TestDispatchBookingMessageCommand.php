<?php

// @codeCoverageIgnoreStart

namespace App\Command;

use App\Entity\Main\Booking;
use App\Message\CreateBookingMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:test:dispatchbooking',
    description: 'Test dispatch booking to message queue',
)]
class TestDispatchBookingMessageCommand extends Command
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select the type of booking',
            ['Instant booking', 'Acceptance flow booking'],
            0
        );
        $question->setErrorMessage('Selection %s is invalid.');

        $email = $io->ask('Enter author email');
        $from = $io->ask('From (i.e 2022-12-24 20:00:00)');
        $to = $io->ask('To (i.e 2022-12-24 20:00:00)');
        $type = $helper->ask($input, $output, $question);

        switch ($type) {
            case 'Instant booking':
                $this->bus->dispatch(new CreateBookingMessage($this->createBooking($email, 'instant', $from, $to)));
                break;

            case 'Acceptance flow booking':
                $this->bus->dispatch(new CreateBookingMessage($this->createBooking($email, 'acceptance_flow', $from, $to)));
                break;
        }
        $output->writeln('Added booking to message queue');

        return Command::SUCCESS;
    }

    /**
     * @param $email
     * @param $type
     * @param $from
     * @param $to
     *
     * @return Booking
     *
     * @throws \Exception
     */
    private function createBooking($email, $type, $from, $to): Booking
    {
        $resourceEmail = 'instant' === $type ? 'DOKK1-Lokale-Test1@aarhus.dk' : 'dokk1-lokale-test2@aarhus.dk';
        $booking = new Booking();
        $booking->setBody('test');
        $booking->setSubject('test');
        $booking->setResourceName('test');
        $booking->setResourceEmail($resourceEmail);
        $booking->setStartTime(new \DateTime($from));
        $booking->setEndTime(new \DateTime($to));
        $booking->setUserPermission('citizen');

        $metaData = [
            'meta_data_4' => '1, 2, 3',
            'meta_data_5' => 'a1, b2, c3',
            'meta_data_1' => 'This is a metadata field',
            'meta_data_2' => 'This is also metadata',
            'meta_data_3' => 'Lorem ipsum metadata',
        ];

        $booking->setUserName('Testes');
        $booking->setUserMail($email);
        $booking->setUserId('1234567');
        $booking->setMetaData($metaData);

        return $booking;
    }
}
// @codeCoverageIgnoreEnd

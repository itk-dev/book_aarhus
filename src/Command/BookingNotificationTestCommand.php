<?php

namespace App\Command;

use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Service\NotificationServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:bookingnotification:test',
    description: 'Test a booking notification',
)]
class BookingNotificationTestCommand extends Command
{
    public function __construct(private NotificationServiceInterface $notificationService)
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
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select the type of booking notification to test',
            ['New booking success', 'Booking changed', 'Booking failed'],
            0
        );
        $question->setErrorMessage('Selection %s is invalid.');

        $email = $io->ask('Enter email to send mail to.');
        $type = $helper->ask($input, $output, $question);

        switch ($type) {
            case 'New booking success':
                $this->notificationService->sendBookingNotification($this->createBooking($email), $this->createResource(), 'success');
                $output->writeln('Sent "'.$type.'" mail to '.$email);
                break;

            case 'Booking changed':
                $this->notificationService->sendBookingNotification($this->createBooking($email), $this->createResource(), 'booking_changed');
                $output->writeln('Sent "'.$type.'" mail to '.$email);

                break;

            case 'Booking failed':
                $this->notificationService->sendBookingNotification($this->createBooking($email), $this->createResource(), 'booking_failed');
                $output->writeln('Sent "'.$type.'" mail to '.$email);

                break;
        }

        return Command::SUCCESS;
    }

    private function createBooking($email): Booking
    {
        $booking = new Booking();
        $booking->setBody('test');
        $booking->setSubject('test');
        $booking->setResourceName('test');
        $booking->setResourceEmail('test@bookaarhus.local.itkdev.dk');
        $booking->setStartTime(new \DateTime());
        $booking->setEndTime(new \DateTime());

        $submissionData = [
            'subject' => 'test1',
            'resourceId' => 'test@bookaarhus.local.itkdev.dk',
            'start' => '2022-08-18T10:00:00.000Z',
            'end' => '2022-08-18T10:30:00.000Z',
            'userId' => 'test4',
            'formElement' => 'booking_element',
            'name' => 'auther1',
            'email' => $email,
        ];

        $metaData = [
            'meta_data_4' => '1, 2, 3',
            'meta_data_5' => 'a1, b2, c3',
            'meta_data_1' => 'This is a metadata field',
            'meta_data_2' => 'This is also metadata',
            'meta_data_3' => 'Lorem ipsum metadata',
        ];

        $booking->setWebformSubmission(json_encode([
            'submissiondata' => $submissionData,
            'metaData' => $metaData,
        ]));

        return $booking;
    }

    private function createResource(): AAKResource
    {
        $res = new AAKResource();
        $res->setResourceMail('test@bookaarhus.local.itkdev.dk');
        $res->setResourceName('test');
        $res->setResourceDescription('desc');
        $res->setResourceEmailText('emailtext');
        $res->setLocation('LOCATION1');
        $res->setWheelchairAccessible(true);
        $res->setVideoConferenceEquipment(false);
        $res->setUpdateTimestamp(new \DateTime());
        $res->setMonitorEquipment(false);
        $res->setCatering(false);
        $res->setAcceptanceFlow(false);
        $res->setCapacity(10);
        $res->setPermissionBusinessPartner(true);
        $res->setPermissionCitizen(true);
        $res->setPermissionEmployee(true);
        $res->setHasWhitelist(false);

        return $res;
    }
}

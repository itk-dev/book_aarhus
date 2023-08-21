<?php

// @codeCoverageIgnoreStart

namespace App\Command;

use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Enum\NotificationTypeEnum;
use App\Service\NotificationServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test:bookingnotification',
    description: 'Test a booking notification',
)]
class TestBookingNotificationCommand extends Command
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
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select the type of booking notification to test',
            ['New booking success', 'Booking request received', 'Booking changed', 'Booking failed'],
            0
        );
        $question->setErrorMessage('Selection %s is invalid.');

        $email = $io->ask('Enter email to send mail to.');
        $type = $helper->ask($input, $output, $question);

        switch ($type) {
            case 'New booking success':
                $this->notificationService->sendBookingNotification($this->createBooking($email), $this->createResource(), NotificationTypeEnum::SUCCESS);
                $output->writeln('Sent "'.$type.'" mail to '.$email);
                break;

            case 'Booking request received':
                $this->notificationService->sendBookingNotification($this->createBooking($email), $this->createResource(), NotificationTypeEnum::REQUEST_RECEIVED);
                $output->writeln('Sent "'.$type.'" mail to '.$email);
                break;

            case 'Booking failed':
                $this->notificationService->sendBookingNotification($this->createBooking($email), $this->createResource(), NotificationTypeEnum::FAILED);
                $output->writeln('Sent "'.$type.'" mail to '.$email);

                break;
        }

        return Command::SUCCESS;
    }

    /**
     * @param $email
     *
     * @return Booking
     */
    private function createBooking($email): Booking
    {
        $booking = new Booking();
        $booking->setBody('test');
        $booking->setSubject('test');
        $booking->setResourceName('test');
        $booking->setResourceEmail('test@bookaarhus.local.itkdev.dk');
        $booking->setStartTime(new \DateTime('2022-12-24 19:00:00'));
        $booking->setEndTime(new \DateTime('2022-12-24 23:00:00'));

        $submissionData = [
            'subject' => 'test1',
            'resourceId' => 'test@bookaarhus.local.itkdev.dk',
            'start' => '2022-12-24T20:00:00.000Z',
            'end' => '2022-12-24T23:00:00.000Z',
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

        $booking->setUserName($submissionData['name']);
        $booking->setUserMail($submissionData['email']);
        $booking->setMetaData($metaData);

        return $booking;
    }

    /**
     * @return AAKResource
     */
    private function createResource(): AAKResource
    {
        $res = new AAKResource();
        $res->setResourceMail('test@bookaarhus.local.itkdev.dk');
        $res->setResourceName('test');
        $res->setResourceDescription('Resource description as shown in the booking app.');
        $res->setResourceEmailText('Resource specific text from resource db.');
        $res->setLocation('LOCATION1');
        $res->setGeoCoordinates('56.175895100,10.191482000');
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
// @codeCoverageIgnoreEnd

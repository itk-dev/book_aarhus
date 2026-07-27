<?php

// @codeCoverageIgnoreStart

namespace App\Command;

use App\Exception\BookingCreateConflictException;
use App\Exception\WebformSubmissionRetrievalException;
use App\Service\MetricsHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsCommand(
    name: 'app:test:metrics-exception',
    description: 'Simulate the exceptions tracked in exception_total to observe the counters at /metrics',
)]
class TestMetricsExceptionCommand extends Command
{
    /**
     * The class constants must match the ones passed to incExceptionTotal() in
     * production code, so the resulting labels are identical. Keys equal the
     * exception label value exported at /metrics.
     */
    private const array TRACKED_EXCEPTIONS = [
        'unrecoverable_message_handling_exception' => UnrecoverableMessageHandlingException::class,
        'recoverable_message_handling_exception' => RecoverableMessageHandlingException::class,
        'transport_exception_interface' => TransportExceptionInterface::class,
        'booking_create_conflict_exception' => BookingCreateConflictException::class,
        'webform_submission_retrieval_exception' => WebformSubmissionRetrievalException::class,
        'bad_request_exception' => BadRequestException::class,
        'bad_request_http_exception' => BadRequestHttpException::class,
        'access_denied_http_exception' => AccessDeniedHttpException::class,
        'not_found_http_exception' => NotFoundHttpException::class,
        'exception' => \Exception::class,
        'error' => \Error::class,
    ];

    public function __construct(private readonly MetricsHelper $metricsHelper)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::OPTIONAL, 'Exception label to increment (omit to list available types)', null, array_keys(self::TRACKED_EXCEPTIONS))
            ->addOption('all', null, InputOption::VALUE_NONE, 'Increment all tracked exception types')
            ->addOption('count', null, InputOption::VALUE_REQUIRED, 'Number of increments per type', '1');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $type = $input->getArgument('type');
        $type = is_string($type) ? $type : null;
        $all = $input->getOption('all');
        $count = max(1, (int) $input->getOption('count'));

        if (null === $type && !$all) {
            $io->table(
                ['Type (= exception label)', 'Class'],
                array_map(null, array_keys(self::TRACKED_EXCEPTIONS), array_values(self::TRACKED_EXCEPTIONS)),
            );
            $io->writeln(sprintf('Usage: %s <type> [--count=N], or --all', (string) $this->getName()));

            return Command::SUCCESS;
        }

        if ($all) {
            $types = self::TRACKED_EXCEPTIONS;
        } elseif (isset(self::TRACKED_EXCEPTIONS[$type])) {
            $types = [$type => self::TRACKED_EXCEPTIONS[$type]];
        } else {
            $io->error(sprintf('Unknown type "%s". Run without arguments to list available types.', $type));

            return Command::INVALID;
        }

        foreach ($types as $label => $class) {
            for ($i = 0; $i < $count; ++$i) {
                $this->metricsHelper->incExceptionTotal($class);
            }

            $io->writeln(sprintf('+%d bookaarhus_exception_total{exception="%s"}', $count, $label));
        }

        return Command::SUCCESS;
    }
}
// @codeCoverageIgnoreEnd

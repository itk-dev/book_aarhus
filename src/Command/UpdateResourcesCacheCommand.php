<?php

namespace App\Command;

use App\Repository\Main\AAKResourceRepository;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsCommand(
    name: 'app:resource:cache',
    description: 'Updates resource cache item',
)]
class UpdateResourcesCacheCommand extends Command
{
    public const CACHE_LIFETIME = 'cache-lifetime';
    public const ENABLE_PROGRESSBAR = 'enable-progressbar';

    public function __construct(
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly CacheInterface $resourceCache,
        private readonly SerializerInterface $serializer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            UpdateResourcesCacheCommand::CACHE_LIFETIME,
            null,
            InputOption::VALUE_OPTIONAL,
            'Cache entry lifetime in seconds. Default 1800 s.',
            60 * 30
        );
        $this->addOption(
            UpdateResourcesCacheCommand::ENABLE_PROGRESSBAR,
            'p',
            InputOption::VALUE_NONE,
            'Enable progress bar. Default false.'
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheLifetime = (int) $input->getOption(UpdateResourcesCacheCommand::CACHE_LIFETIME);
        $enableProgressbar = $input->getOption(UpdateResourcesCacheCommand::ENABLE_PROGRESSBAR);

        $progressBar = new ProgressBar($output, 6);
        $progressBar->setFormat('[%bar%] %elapsed% (%memory%)');

        $enableProgressbar && $progressBar->start();

        $this->resourceCache->delete('resources-');

        $enableProgressbar && $progressBar->advance();

        $this->resourceCache->get('resources-', function (CacheItemInterface $cacheItem) use ($cacheLifetime) {
            $cacheItem->expiresAfter($cacheLifetime);
            $info = $this->aakResourceRepository->getAllByPermission();

            return $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);
        });

        $enableProgressbar && $progressBar->advance();

        $this->resourceCache->delete('resources-businessPartner');

        $enableProgressbar && $progressBar->advance();

        $this->resourceCache->get('resources-businessPartner', function (CacheItemInterface $cacheItem) use ($cacheLifetime) {
            $cacheItem->expiresAfter($cacheLifetime);
            $info = $this->aakResourceRepository->getAllByPermission('businessPartner');

            return $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);
        });

        $enableProgressbar && $progressBar->advance();

        $this->resourceCache->delete('resources-citizen');

        $enableProgressbar && $progressBar->advance();

        $this->resourceCache->get('resources-citizen', function (CacheItemInterface $cacheItem) use ($cacheLifetime) {
            $cacheItem->expiresAfter($cacheLifetime);
            $info = $this->aakResourceRepository->getAllByPermission('citizen');

            return $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);
        });

        $enableProgressbar && $progressBar->advance();

        $enableProgressbar && $progressBar->finish();

        $output->writeln('');

        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Interface\ResourceServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:resource:cache',
    description: 'Updates resource cache item',
)]
class UpdateResourcesCacheCommand extends Command
{
    public const CACHE_LIFETIME = 'cache-lifetime';
    public const ENABLE_PROGRESSBAR = 'enable-progressbar';

    public function __construct(
        private readonly ResourceServiceInterface $resourceService,
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheLifetime = (int) $input->getOption(UpdateResourcesCacheCommand::CACHE_LIFETIME);
        $enableProgressbar = $input->getOption(UpdateResourcesCacheCommand::ENABLE_PROGRESSBAR);

        $progressBar = new ProgressBar($output, 3);
        $progressBar->setFormat('[%bar%] %elapsed% (%memory%)');

        $enableProgressbar && $progressBar->start();

        $this->resourceService->removeResourcesCacheEntry();
        $this->resourceService->getAllResources(null, $cacheLifetime);

        $enableProgressbar && $progressBar->advance();

        $this->resourceService->removeResourcesCacheEntry('businessPartner');
        $this->resourceService->getAllResources('businessPartner', $cacheLifetime);

        $enableProgressbar && $progressBar->advance();

        $this->resourceService->removeResourcesCacheEntry('citizen');
        $this->resourceService->getAllResources('citizen', $cacheLifetime);

        $enableProgressbar && $progressBar->advance();

        $enableProgressbar && $progressBar->finish();

        $enableProgressbar && $output->writeln('');

        return Command::SUCCESS;
    }
}

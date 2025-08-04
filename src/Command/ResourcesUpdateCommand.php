<?php

namespace App\Command;

use App\Service\ResourceService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:resources:update',
    description: 'Update all resources',
)] class ResourcesUpdateCommand extends Command
{
    public function __construct(
        private readonly ResourceService $resourceService)
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->resourceService->update();

        return Command::SUCCESS;
    }
}

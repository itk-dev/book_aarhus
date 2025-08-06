<?php

namespace App\Command;

use App\Repository\ResourceRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:resource:display',
    description: 'Display info about a resource',
)]
class DisplayResourceCommand extends Command
{
    public function __construct(
        private readonly ResourceRepository $resourceRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('resourceEmail', InputArgument::REQUIRED, 'The resource to get information about.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $resourceEmail = $input->getArgument('resourceEmail');
        $info = $this->resourceRepository->findOneByEmail($resourceEmail);
        isset($info) ? dump($info) : $output->writeln('<error>Resource not found.</error>');

        return Command::SUCCESS;
    }
}

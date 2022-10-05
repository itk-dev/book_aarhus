<?php

namespace App\Command;

use App\Service\ApiKeyUserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:auth:delete-apikey',
    description: 'Delete an apikey',
)]
class AuthDeleteApikeyCommand extends Command
{
    public function __construct(private ApiKeyUserService $apiKeyUserService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Id of apikey to remove');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Delete without confirm', false);
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $id = $input->getArgument('id');
        $force = $input->getOption('force');

        if (!$force) {
            $confirmed = $io->confirm('Remove ApiKeyUser with id: '.$id.'?', false);
            if (!$confirmed) {
                $io->error('Aborted');

                return Command::FAILURE;
            }
        }

        $this->apiKeyUserService->removeApiKey($id);

        $io->success('ApiKeyUser deleted');

        return Command::SUCCESS;
    }
}

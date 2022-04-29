<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\Helper;

class TestMicGraph extends Command {
  // Command name.
  protected static $defaultName = 'app:test-microsoft-graph';

  // Command description.
  protected static $defaultDescription = 'Test microsoft graph connection.';

  // Helper service.
  private Helper $helper;

  /**
   * TestMicGraph constructor.
   * .
   * @param Helper $helper
   */
  public function __construct(Helper $helper)
  {
    $this->helper = $helper;

    parent::__construct();
  }

  /**
   * Execute microsoft graph test.
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   * @return int
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $token = $this->helper->connect();
    if (empty($token)) {
      return Command::FAILURE;
    }
    $reply = $this->helper->createRequest($token, $input->getArgument('endpoint'));
    print_r($reply);
    return Command::SUCCESS;
  }

  /**
   * Configure command.
   */
  protected function configure(): void
  {
    $this->setHelp('This command tests your microsoft graph connection.');
    // @todo add endpoint as argument i.e. /me
    $this->addArgument('endpoint', InputArgument::REQUIRED, 'Microsoft graph endpoint. (i.e. /me)');
  }
}
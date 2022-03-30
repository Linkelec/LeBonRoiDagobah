<?php

namespace App\Command;

use App\Service\TwitterApiService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BotCommand extends Command
{
    private $twitterApi;
    protected static $defaultName = 'bot:post';

    public function __construct(TwitterApiService $twitterApi)
    {
        parent::__construct();
        $this->twitterApi = $twitterApi;
    }

    protected function configure()
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->twitterApi->checkTweet();
        return Command::SUCCESS;
    }
}
<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Communication\Console;

use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \SprykerCommunity\Zed\WebhookProcessor\Business\WebhookProcessorFacadeInterface getFacade()
 */
class WebhookInboxWorkerConsole extends Console
{
    /**
     * @var string
     */
    protected const string COMMAND_NAME = 'webhook-processor:inbox-worker:start';

    /**
     * @var string
     */
    protected const string OPTION_TIME_LIMIT = 'time-limit';

    /**
     * @var int
     */
    protected const int DEFAULT_TIME_LIMIT = 60;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName(static::COMMAND_NAME)
            ->setDescription('Processes buffered webhook messages from the Redis inbox and routes them to their configured queues.')
            ->addOption(
                static::OPTION_TIME_LIMIT,
                null,
                InputOption::VALUE_OPTIONAL,
                'Maximum runtime in seconds.',
                static::DEFAULT_TIME_LIMIT,
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timeLimit = (int)$input->getOption(static::OPTION_TIME_LIMIT);

        $this->getFacade()->runWebhookInboxWorker($timeLimit);

        return static::CODE_SUCCESS;
    }
}

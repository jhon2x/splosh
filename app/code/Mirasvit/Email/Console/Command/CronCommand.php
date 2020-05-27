<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Console\Command;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Mirasvit\Email\Cron\CleanHistoryCron;
use Mirasvit\Email\Cron\HandleEventsCron;
use Mirasvit\Email\Cron\SendQueueCron;
use Mirasvit\Email\Cron\CleanHistoryCronFactory;
use Mirasvit\Email\Cron\HandleEventsCronFactory;
use Mirasvit\Email\Cron\SendQueueCronFactory;

class CronCommand extends Command
{
    /**
     * @var State
     */
    protected $state;
    /**
     * @var CleanHistoryCronFactory
     */
    private $cleanHistoryCronFactory;
    /**
     * @var HandleEventsCronFactory
     */
    private $handleEventsCronFactory;
    /**
     * @var SendQueueCronFactory
     */
    private $sendQueueCronFactory;

    public function __construct(
        State $state,
        CleanHistoryCronFactory $cleanHistoryCronFactory,
        HandleEventsCronFactory $handleEventsCronFactory,
        SendQueueCronFactory $sendQueueCronFactory
    ) {
        $this->state = $state;

        parent::__construct();
        $this->cleanHistoryCronFactory = $cleanHistoryCronFactory;
        $this->handleEventsCronFactory = $handleEventsCronFactory;
        $this->sendQueueCronFactory = $sendQueueCronFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:email:cron')
            ->setDescription('Run cron jobs')
            ->setDefinition([]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /** @var CleanHistoryCron $cleanHistoryCron */
        $cleanHistoryCron = $this->cleanHistoryCronFactory->create();
        /** @var HandleEventsCron $handleEventsCron */
        $handleEventsCron = $this->handleEventsCronFactory->create();
        /** @var SendQueueCron $sendQueueCron */
        $sendQueueCron = $this->sendQueueCronFactory->create();

        $this->state->setAreaCode('global');

        $output->write('Cron "Fetch Events"....');
        $handleEventsCron->execute();
        $output->writeln('done');

        $output->write('Cron "Send Queue"....');
        $sendQueueCron->execute();
        $output->writeln('done');

        $output->write('Cron "Clean History"....');
        $cleanHistoryCron->execute();
        $output->writeln('done');
    }
}

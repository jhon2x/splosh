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
 * @package   mirasvit/module-message-queue
 * @version   1.0.9
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Mq\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Filesystem\DirectoryList;
use Mirasvit\Mq\Api\Service\QueueServiceInterface;

class SubscribeCommand extends Command
{
    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var QueueServiceInterface
     */
    private $queueService;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        Filesystem $filesystem,
        AppState $appState,
        QueueServiceInterface $queueService
    ) {
        $this->appState = $appState;
        $this->queueService = $queueService;
        $this->filesystem = $filesystem;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:message-queue:subscribe')
            ->setDescription('Subscribe')
            ->setDefinition([]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('global');
        } catch (\Exception $e) {
            # already set by another module
        }

        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $file = $dir->openFile('mq.lock');

        try {
            if ($file->lock(\LOCK_EX | \LOCK_NB)) {
                $this->queueService->subscribe(10 * 60);
                $file->unlock();
            }
        } catch (\Exception $e) { /* ignore flock exception */ }
    }
}

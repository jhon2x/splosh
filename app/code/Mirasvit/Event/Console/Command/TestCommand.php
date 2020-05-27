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
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        State $appState,
        ObjectManagerInterface $objectManager
    ) {
        $this->appState = $appState;
        $this->objectManager = $objectManager;

        parent::__construct();
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:event:test')
            ->setDescription('For testing purpose')
            ->setDefinition([]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $value, OutputInterface $output)
    {
        /** @var \Mirasvit\Event\Api\Repository\EventRepositoryInterface $repository */
        $repository = $this->objectManager->create('Mirasvit\Event\Api\Repository\EventRepositoryInterface');

        foreach ($repository->getCollection() as $event) {
            $instance = $repository->getInstance($event->getIdentifier());

             $output->writeln($instance->toString($event->getParams()));
        }
    }
}

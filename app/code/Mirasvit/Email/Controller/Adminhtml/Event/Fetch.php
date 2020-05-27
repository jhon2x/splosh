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



namespace Mirasvit\Email\Controller\Adminhtml\Event;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\Email\Controller\Adminhtml\Event;
use Mirasvit\Email\Cron\HandleEventsCron;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

class Fetch extends Event
{
    /**
     * @var HandleEventsCron
     */
    protected $handleEventsCron;

    public function __construct(
        HandleEventsCron $handleEventsCron,
        EventRepositoryInterface $eventRepository,
        Registry $registry,
        Context $context
    ) {
        $this->handleEventsCron = $handleEventsCron;

        parent::__construct($eventRepository, $registry, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $this->handleEventsCron->execute(); // Register events and process them with triggers

        $this->messageManager->addSuccessMessage(__('Completed.'));

        return $resultRedirect->setPath('*/*');
    }
}

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



namespace Mirasvit\Email\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mirasvit\Email\Api\Service\SessionInitiatorInterface;
use Mirasvit\Email\Controller\Adminhtml\Queue;
use Mirasvit\Email\Model\QueueFactory;
use Magento\Framework\App\ResourceConnection;

class Index extends Queue
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    protected $resource;
    /**
     * @var SessionInitiatorInterface
     */
    private $sessionInitiator;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param SessionInitiatorInterface            $sessionInitiator
     * @param QueueFactory                         $queueRepository
     * @param TimezoneInterface                    $timezone
     * @param ResourceConnection                   $resourceConnection
     * @param Registry                             $registry
     * @param Context                              $context
     */
    public function __construct(
        \Magento\Framework\Data\Form\FormKey $formKey,
        SessionInitiatorInterface $sessionInitiator,
        QueueFactory $queueRepository,
        TimezoneInterface $timezone,
        ResourceConnection $resourceConnection,
        Registry $registry,
        Context $context
    ) {
        $this->formKey = $formKey;
        $this->sessionInitiator = $sessionInitiator;
        $this->timezone = $timezone;
        $this->resource = $resourceConnection;
        $this->connection = $resourceConnection->getConnection();

        parent::__construct($queueRepository, $registry, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->sessionInitiator->set($this->formKey->getFormKey()); // init session key to make possible to send emails

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $mysqlTime = $this->connection->fetchOne('SELECT CURRENT_TIMESTAMP');

        $cronJob = $this->connection->select()->from(
            $this->resource->getTableName("cron_schedule"),
            [new \Zend_Db_Expr("MAX(executed_at)")]
        )
            ->limit(1);
        $lastCronRun = $this->connection->fetchOne($cronJob);


        $localTime = $this->timezone->formatDateTime(
            new \DateTime(null, new \DateTimeZone($this->timezone->getConfigTimezone())),
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::SHORT,
            null
        );
        $mysqlTime = $this->timezone->formatDateTime(
            $mysqlTime,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::SHORT,
            null,
            'UTC'
        );

        if (!$lastCronRun) {
            $cronTime = "<b>Cron job is not configured</b>";
        } else {
            $cronTime = $this->timezone->formatDateTime(
                new \DateTime($lastCronRun, new \DateTimeZone($this->timezone->getConfigTimezone())),
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::SHORT,
                null
            );
        }

        $this->context->getMessageManager()->addNotice(__(
            "Local time: %1
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                GMT/Mysql time: %2
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Last cron job run time: %3",
            $localTime,
            $mysqlTime,
            $cronTime
        ));

        $this->initPage($resultPage);

        return $resultPage;
    }
}

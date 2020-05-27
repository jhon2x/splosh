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



namespace Mirasvit\Email\Model\Queue;

use Magento\Framework\App\Area;
use Magento\Framework\App\ProductMetadataInterface as ProductMetadata;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Mail\MessageFactory as MailMessageFactory;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Service\Queue\MailModifierInterface;
use Mirasvit\Email\Controller\RegistryConstants;
use Mirasvit\Email\Helper\Data as Helper;
use Mirasvit\Email\Model\Config;
use Mirasvit\Email\Model\Queue;
use Mirasvit\Email\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Mirasvit\Email\Model\Unsubscription;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Sender
{
    /**
     * @var Unsubscription
     */
    protected $unsubscription;

    /**
     * @var QueueCollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var MailMessageFactory
     */
    protected $mailMessageFactory;

    /**
     * @var Emulation
     */
    protected $appEmulation;

    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var \Magento\Framework\Mail\TransportInterfaceFactory
     */
    protected $mailTransportFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var  MailModifierInterface[]
     */
    private $modifiers;

    /**
     * @var ProductMetadata
     */
    private $productMetadata;

    public function __construct(
        Registry $registry,
        TransportBuilder $transportBuilder,
        Unsubscription $unsubscription,
        QueueCollectionFactory $queueCollectionFactory,
        Config $config,
        DateTime $date,
        MailMessageFactory $mailMessageFactory,
        Emulation $appEmulation,
        AppState $appState,
        TransportInterfaceFactory $mailTransportFactory,
        StoreManagerInterface $storeManager,
        Helper $helper,
        ProductMetadata $productMetadata,
        array $modifiers = []
    ) {
        $this->registry               = $registry;
        $this->unsubscription         = $unsubscription;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->config                 = $config;
        $this->date                   = $date;
        $this->mailMessageFactory     = $mailMessageFactory;

        $this->appEmulation         = $appEmulation;
        $this->appState             = $appState;
        $this->mailTransportFactory = $mailTransportFactory;
        $this->storeManager         = $storeManager;
        $this->helper               = $helper;
        $this->transportBuilder     = $transportBuilder;
        $this->modifiers            = $modifiers;
        $this->productMetadata      = $productMetadata;
    }

    /**
     * Send mail
     *
     * @param Queue $queue
     * @param bool  $force
     *
     * @return bool
     */
    public function send($queue, $force = false)
    {
        if (!$this->canSend($queue) && !$force) {
            return false;
        }

        // register current email queue model instance
        $this->registry->register(RegistryConstants::CURRENT_QUEUE, $queue, true);

        $this->appEmulation->startEnvironmentEmulation($queue->getArgs('store_id'), Area::AREA_FRONTEND, true);
        $subject = $queue->getMailSubject();
        $this->appEmulation->stopEnvironmentEmulation();

        $this->appEmulation->startEnvironmentEmulation($queue->getArgs('store_id'), Area::AREA_FRONTEND, true);

        $body = $queue->getMailContent();

        foreach ($this->modifiers as $modifier) {
            $body = $modifier->modifyContent($queue, $body);
        }

        $body = $this->helper->prepareQueueContent($body, $queue);
        $this->appEmulation->stopEnvironmentEmulation();

        $this->appEmulation->startEnvironmentEmulation($queue->getArgs('store_id'), Area::AREA_FRONTEND, true);

        $recipients = explode(',', $queue->getRecipientEmail());
        if ($this->config->isSandbox() && !$queue->getArg('force')) {
            $recipients = explode(',', $this->config->getSandboxEmail());
        }

        foreach ($recipients as $index => $email) {
            $name = $queue->getRecipientName();
            if (count($recipients) > 1) {
                $name .= ' - ' . ($index + 1);
            }
            unset($recipients[$index]);
            $recipients[$name] = $email;
        }

        //trim spaces and remove all empty items
        $copyTo = array_filter(array_map('trim', explode(',', $queue->getTrigger()->getCopyEmail())));
        foreach ($copyTo as $bcc) {
            $this->transportBuilder->addBcc($bcc);
        }

        $this->transportBuilder
            ->setSubject($subject)
            ->setBody($body)
            ->setReplyTo($queue->getSenderEmail(), $queue->getSenderName());

        $magentoVersion = $this->productMetadata->getVersion();
        if (version_compare($magentoVersion, '2.3.0', '>=')) {
            $this->transportBuilder->setFrom([$queue->getSenderEmail() => $queue->getSenderName()]);
        } else {
            $this->transportBuilder->setFrom($queue->getSenderEmail(), $queue->getSenderName());
            $this->transportBuilder
                ->setMessageType(MessageInterface::TYPE_HTML);
        }

        foreach ($recipients as $name => $email) {
            $this->transportBuilder->addTo($email, $name);
        }

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();

        $queue->delivery();

        $this->appEmulation->stopEnvironmentEmulation();

        return true;
    }

    /**
     * Check rules and other conditions
     *
     * @param Queue $queue
     *
     * @return bool
     */
    protected function canSend($queue)
    {
        $args = $queue->getArgs();
        if ($queue->getArg('force')) {
            return true;
        }

        if (time() - strtotime($queue->getScheduledAt()) > 2 * 24 * 60 * 60) {
            $queue->miss(__('Scheduled at %1, attempt to send after 2 days', $queue->getScheduledAt()));

            return false;
        }

        // check unsubscription
        if ($this->unsubscription->isUnsubscribed($queue->getRecipientEmail(), $queue->getTriggerId())) {
            $queue->unsubscribe(__('Customer %1 is unsubscribed', $queue->getRecipientEmail()));

            return false;
        }

        // check rules
        if (!$queue->getTrigger()->validateRules($args)) {
            $queue->cancel(__('Canceled by trigger rules'));

            return false;
        }

        // check limitation
        if (!$this->isValidByLimit($args)) {
            $queue->cancel(__('Canceled by global limitation settings'));

            return false;
        }

        if (!$queue->getTemplate()) {
            $queue->cancel(__('Missed Template'));

            return false;
        }

        return true;
    }

    /**
     * Is valid by limit
     *
     * @param array $args
     *
     * @return bool
     */
    protected function isValidByLimit($args)
    {
        $result     = true;
        $emailLimit = $this->config->getEmailLimit();
        $hourLimit  = $this->config->getEmailLimitPeriod() * 60 * 60;
        if (in_array(0, [$emailLimit, $hourLimit])) {
            return $result;
        }

        $gmtTimestampMinusLimit = $this->date->timestamp() - $hourLimit;
        $filterDateFrom         = $this->date->gmtDate(null, $gmtTimestampMinusLimit);

        $queues = $this->queueCollectionFactory->create()
            ->addFieldToFilter('recipient_email', $args['customer_email'])
            ->addFieldToFilter('status', QueueInterface::STATUS_SENT)
            ->addFieldToFilter('updated_at', ['gt' => $filterDateFrom]);

        if ($queues->count() >= $emailLimit) {
            $result = false;
        }

        return $result;
    }
}

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



namespace Mirasvit\Email\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Service\ErrorHandlerInterface;
use Mirasvit\Email\Model\Queue\Sender;
use Mirasvit\Email\Model\Trigger\ChainFactory;
use Mirasvit\EmailDesigner\Api\Service\TemplateProcessorInterface;
use Mirasvit\Email\Helper\Serializer;

/**
 * @method bool hasStatus()
 * @method $this setStatusMessage($message)
 * @method bool hasCreatedAt()
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Queue extends AbstractModel implements QueueInterface
{
    /**
     * @var array
     */
    protected $args;

    /**
     * @var Trigger
     */
    protected $trigger;

    /**
     * @var Trigger\Chain
     */
    protected $emailChain;

    /**
     * @var TriggerFactory
     */
    protected $triggerFactory;

    /**
     * @var ChainFactory
     */
    protected $chainFactory;

    /**
     * @var Sender
     */
    protected $sender;
    /**
     * @var ErrorHandlerInterface
     */
    private $errorHandler;
    /**
     * @var TemplateProcessorInterface
     */
    private $templateProcessor;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        TemplateProcessorInterface $templateProcessor,
        ErrorHandlerInterface      $errorHandler,
        Sender                     $sender,
        TriggerFactory             $triggerFactory,
        ChainFactory               $chainFactory,
        Context                    $context,
        Registry                   $registry,
        Serializer                 $serializer
    ) {
        $this->templateProcessor = $templateProcessor;
        $this->errorHandler      = $errorHandler;
        $this->triggerFactory    = $triggerFactory;
        $this->chainFactory      = $chainFactory;
        $this->sender            = $sender;
        $this->serializer        = $serializer;

        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Mirasvit\Email\Model\ResourceModel\Queue::class);
    }

    /**
     * Trigger model
     *
     * @return Trigger
     */
    public function getTrigger()
    {
        if ($this->trigger == null) {
            $this->trigger = $this->triggerFactory->create()
                ->load($this->getTriggerId());
        }

        return $this->trigger;
    }

    /**
     * Chain model
     *
     * @return Trigger\Chain
     */
    public function getChain()
    {
        if ($this->emailChain === null) {
            $this->emailChain = $this->chainFactory->create()
                ->load($this->getChainId());
        }

        return $this->emailChain;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->getChain()->getTemplate();
    }

    /**
     * {@inheritdoc}
     */
    public function getArgs($key = null)
    {
        if ($this->args == null) {
            $this->args = $this->serializer->unserialize($this->getData('args_serialized'));
            $this->args['trigger'] = $this->getTrigger();
            $this->args['chain'] = $this->getChain();
            $this->args['queue'] = $this;
        }

        if ($key) {
            if (isset($this->args[$key])) {
                return $this->args[$key];
            } else {
                return false;
            }
        }

        return $this->args;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getArg($key)
    {
        return $this->getArgs($key);
    }

    /**
     * Email subject
     *
     * @return string
     */
    public function getMailSubject()
    {
        if (!$this->getSubject()) {
            $this->errorHandler->registerErrorHandler();
            $this->setSubject($this->templateProcessor->processSubject($this->getTemplate(), $this->getArgs()));

            if (isset($this->args['is_test'])) {
                $subject = $this->getSubject()
                    . ' ' . __('[Test Mail. Store #%1 %2]', $this->args['store_id'], date('d/m/Y H:i:s'));

                $this->errorHandler->restoreErrorHandler();

                return $subject;
            }

            $this->errorHandler->restoreErrorHandler();
        }

        return $this->getSubject();
    }

    /**
     * Email contenct
     *
     * @return string
     */
    public function getMailContent()
    {
        if (!$this->getContent()) {
            $this->errorHandler->registerErrorHandler();

            $this->setContent($this->templateProcessor->processTemplate($this->getTemplate(), $this->getArgs()));

            $this->errorHandler->restoreErrorHandler();
        }

        return $this->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function send($force = false)
    {
        $this->sender->send($this, $force);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function pending($message = '')
    {
        $this->setStatus(self::STATUS_PENDING)
            ->setStatusMessage($message)
            ->save();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delivery($message = '')
    {
        $this->setSentAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT))
            ->setStatus(self::STATUS_SENT)
            ->setStatusMessage($message)
            ->save();

        $this->_eventManager->dispatch('email_queue_delivery', ['queue' => $this]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function miss($message = '')
    {
        $this->setStatus(self::STATUS_MISSED)
            ->setStatusMessage($message)
            ->save();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cancel($message = '')
    {
        $this->setStatus(self::STATUS_CANCELED)
            ->setStatusMessage($message)
            ->save();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function error($message = '')
    {
        $this->setStatus(self::STATUS_ERROR)
            ->setStatusMessage($message)
            ->save();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribe($message = '')
    {
        $this->setStatus(self::STATUS_UNSUBSCRIBED)
            ->setStatusMessage($message)
            ->save();

        return $this;
    }

    /**
     * Reset queue data
     *
     * @param string $message
     * @return $this
     */
    public function reset($message = '')
    {
        $this->setStatus(self::STATUS_PENDING)
            ->setStatusMessage($message)
            ->setSentAt(null)
            ->setContent(null)
            ->save();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTriggerId()
    {
        return $this->getData(self::TRIGGER_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setTriggerId($triggerId)
    {
        $this->setData(self::TRIGGER_ID, $triggerId);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getChainId()
    {
        return $this->getData(self::CHAIN_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setChainId($chainId)
    {
        $this->setData(self::CHAIN_ID, $chainId);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUniqKey()
    {
        return $this->getData(self::UNIQUE_KEY);
    }

    /**
     * {@inheritDoc}
     */
    public function setUniqKey($uniqKey)
    {
        $this->setData(self::UNIQUE_KEY, $uniqKey);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUniqHash()
    {
        return $this->getData(self::UNIQUE_HASH);
    }

    /**
     * {@inheritDoc}
     */
    public function setUniqHash($uniqHash)
    {
        $this->setData(self::UNIQUE_HASH, $uniqHash);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getScheduledAt()
    {
        return $this->getData(self::SCHEDULED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setScheduledAt($scheduledAt)
    {
        $this->setData(self::SCHEDULED_AT, $scheduledAt);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSentAt()
    {
        return $this->getData(self::SENT_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setSentAt($sentAt)
    {
        $this->setData(self::SENT_AT, $sentAt);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttemtpsNumber()
    {
        return $this->getData(self::ATTEMPTS_NUMBER);
    }

    /**
     * {@inheritDoc}
     */
    public function setAttemtpsNumber($attemtpsNumber)
    {
        $this->setData(self::ATTEMPTS_NUMBER, $attemtpsNumber);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSenderEmail()
    {
        return $this->getData(self::SENDER_EMAIL);
    }

    /**
     * {@inheritDoc}
     */
    public function setSenderEmail($senderEmail)
    {
        $this->setData(self::SENDER_EMAIL, $senderEmail);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSenderName()
    {
        return $this->getData(self::SENDER_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setSenderName($senderName)
    {
        $this->setData(self::SENDER_NAME, $senderName);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRecipientEmail()
    {
        return $this->getData(self::RECIPIENT_EMAIL);
    }

    /**
     * {@inheritDoc}
     */
    public function setRecipientEmail($recipientEmail)
    {
        $this->setData(self::RECIPIENT_EMAIL, $recipientEmail);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRecipientName()
    {
        return $this->getData(self::RECIPIENT_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setRecipientName($recipientName)
    {
        $this->setData(self::RECIPIENT_NAME, $recipientName);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * {@inheritDoc}
     */
    public function setContent($content)
    {
        $this->setData(self::CONTENT, $content);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubject()
    {
        return $this->getData(self::SUBJECT);
    }

    /**
     * {@inheritDoc}
     */
    public function setSubject($subject)
    {
        $this->setData(self::SUBJECT, $subject);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setArgs(array $args)
    {
        $this->setData(self::ARGS, $args);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getArgsSerialized()
    {
        return $this->getData(self::ARGS_SERIALIZED);
    }

    /**
     * {@inheritDoc}
     */
    public function setArgsSerialized($argsSerialized)
    {
        $this->setData(self::ARGS_SERIALIZED, $argsSerialized);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHistory()
    {
        return $this->getData(self::HISTORY);
    }

    /**
     * {@inheritDoc}
     */
    public function setHistory($history)
    {
        $this->setData(self::HISTORY, $history);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);

        return $this;
    }
}

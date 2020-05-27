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
use Magento\Newsletter\Model\SubscriberFactory;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Data\UnsubscriptionInterface;

class Unsubscription extends AbstractModel implements UnsubscriptionInterface
{
    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * Unsubscription constructor.
     *
     * @param SubscriberFactory $subscriberFactory
     * @param Context           $context
     * @param Registry          $registry
     */
    public function __construct(
        SubscriberFactory $subscriberFactory,
        Context $context,
        Registry $registry
    ) {
        $this->subscriberFactory = $subscriberFactory;

        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Mirasvit\Email\Model\ResourceModel\Unsubscription::class);
    }

    /**
     * Unsubscribe.
     *
     * @param string $email
     * @param int    $triggerId
     * @return bool
     */
    public function unsubscribe($email, $triggerId = null)
    {
        $triggerId = intval($triggerId);

        $item = $this->getCollection()
            ->addFieldToFilter(self::EMAIL, $email)
            ->addFieldToFilter(TriggerInterface::ID, [0, $triggerId])
            ->getFirstItem();

        if (!$item->getId() || $triggerId == 0) {
            $item->setTriggerId($triggerId);
        }

        $item->setEmail($email);

        $item->save();

        return true;
    }

    /**
     * Unsubscribe from newsletter.
     *
     * @param string $email
     * @return bool
     */
    public function unsubscribeNewsletter($email)
    {
        $this->subscriberFactory->create()->loadByEmail($email)->unsubscribe();

        return true;
    }

    /**
     * Is already unsubscribed?
     *
     * @param string $email
     * @param int    $triggerId
     * @return bool
     */
    public function isUnsubscribed($email, $triggerId)
    {
        $item = $this->getCollection()
            ->addFieldToFilter(self::EMAIL, $email)
            ->addFieldToFilter(TriggerInterface::ID, [0, $triggerId])
            ->getFirstItem();

        if ($item->getId()) {
            return true;
        }

        return false;
    }
}

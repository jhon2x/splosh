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



namespace Mirasvit\Email\Setup\Upgrade;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Email\Helper\Data;
use Mirasvit\Email\Helper\Serializer;

class UpgradeData106 implements UpgradeDataInterface, VersionableInterface
{
    const VERSION = '1.0.6';

    /**
     * @var TriggerRepositoryInterface
     */
    private $triggerRepository;
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        Data $helper,
        TriggerRepositoryInterface $triggerRepository,
        Serializer               $serializer
    ) {
        $this->triggerRepository = $triggerRepository;
        $this->helper            = $helper;
        $this->serializer        = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * {@inheritDoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->upgradeRules($setup);
        $this->upgradeEvents();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function upgradeRules(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();

        foreach ($this->triggerRepository->getCollection() as $trigger) {
            if ($ruleId = $trigger->getData('run_rule_id')) {
                $select = $connection->select()->from($setup->getTable('mst_email_rule'), ['conditions_serialized'])
                    ->where('rule_id = ?', $ruleId);
                $condition = $connection->fetchOne($select);
                if ($condition) {
                    $condition = $this->serializer->unserialize($condition);

                    $rule['conditions'] = $this->helper->convertConditionsToPost($condition);
                    $conditions = [
                        'Mirasvit\Email\Model\Rule\Condition\Combine'
                        => 'Mirasvit\Event\Model\Rule\Condition\Combine',
                        'Mirasvit\Email\Model\Rule\Condition\Cart'
                        => 'Mirasvit\Event\EventData\Condition\QuoteCondition',
                        'Mirasvit\Email\Model\Rule\Condition\Customer'
                        => 'Mirasvit\Event\EventData\Condition\CustomerCondition',
                        'Mirasvit\Email\Model\Rule\Condition\Order'
                        => 'Mirasvit\Event\EventData\Condition\OrderCondition',
                        'Mirasvit\Email\Model\Rule\Condition\Shipping'
                        => 'Mirasvit\Event\EventData\Condition\AddressShippingCondition',
                        'Mirasvit\Email\Model\Rule\Condition\Wishlist'
                        => 'Mirasvit\Event\EventData\Condition\WishlistCondition',
                        'Mirasvit\Email\Model\Rule\Condition\Product\Subselect'
                        => 'Mirasvit\Event\EventData\Condition\Product\Subselect',
                        'Mirasvit\Email\Model\Rule\Condition\Product\Different'
                        => 'Mirasvit\Event\EventData\Condition\Product\Different',
                    ];

                    // replace condition types
                    foreach ($rule['conditions'] as $key => $condition) {
                        if (isset($condition['type']) && isset($conditions[$condition['type']])) {
                            $rule['conditions'][$key]['type'] = $conditions[$condition['type']];
                        }
                    }

                    $trigger->setRule($rule);

                    $this->triggerRepository->save($trigger);
                }
            }
        }
    }

    /**
     * Update event names to match event names in module-event.
     */
    private function upgradeEvents()
    {
        $events = [
            'customer_registration'              => 'customer_create',
            'customer_subscription|subscribed'   => 'subscription|subscribed',
            'customer_subscription|unsubscribed' => 'subscription|unsubscribed',
            'cart_abandoned'                     => 'quote_abandoned',
            'review_added'                       => 'review_new',
            'wishlist_itemAdded'                 => 'wishlist_item_new',
        ];

        foreach ($this->triggerRepository->getCollection() as $trigger) {
            $cancellationEvents = [];

            if (isset($events[$trigger->getEvent()])) {
                $trigger->setEvent($events[$trigger->getEvent()]);
            }

            foreach ($trigger->getCancellationEvent() as $cancellationEvent) {
                if (isset($events[$cancellationEvent])) {
                    $cancellationEvent = $events[$cancellationEvent];
                }
                $cancellationEvents[] = $cancellationEvent;
            }

            $trigger->setCancellationEvent($cancellationEvents);

            $this->triggerRepository->save($trigger);
        }
    }
}

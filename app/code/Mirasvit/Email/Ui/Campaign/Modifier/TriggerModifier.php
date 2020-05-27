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



namespace Mirasvit\Email\Ui\Campaign\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\QueueRepositoryInterface;
use Mirasvit\Email\Api\Repository\Trigger\ChainRepositoryInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;
use Mirasvit\Event\Model\Rule;
use Mirasvit\Event\Model\RuleFactory;

class TriggerModifier implements ModifierInterface
{
    private $triggerRepository;

    private $chainRepository;

    private $eventRepository;

    private $queueRepository;

    private $urlBuilder;

    private $ruleFactory;

    private $modifierPool;

    public function __construct(
        TriggerRepositoryInterface $triggerRepository,
        ChainRepositoryInterface $chainRepository,
        EventRepositoryInterface $eventRepository,
        QueueRepositoryInterface $queueRepository,
        UrlInterface $urlBuilder,
        RuleFactory $ruleFactory,
        PoolInterface $modifierPool = null
    ) {
        $this->triggerRepository = $triggerRepository;
        $this->chainRepository = $chainRepository;
        $this->eventRepository = $eventRepository;
        $this->queueRepository = $queueRepository;
        $this->urlBuilder = $urlBuilder;
        $this->ruleFactory = $ruleFactory;
        $this->modifierPool = $modifierPool;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    public function modifyData(array $data)
    {
        $trigger = $this->triggerRepository->get($data[TriggerInterface::ID]);

        $extraData = [
            TriggerInterface::IS_ACTIVE   => $trigger->getIsActive(),
            TriggerInterface::ACTIVE_FROM => $trigger->getActiveFrom(),
            TriggerInterface::ACTIVE_TO   => $trigger->getActiveTo(),
            TriggerInterface::DESCRIPTION => $trigger->getDescription(),
            TriggerInterface::EVENT       => $this->getEvent($trigger),
            TriggerInterface::RULE        => $this->getRule($trigger),

            'duplicate_url' => $this->urlBuilder->getUrl(
                'email/trigger/move',
                [
                    '_current'           => 1,
                    TriggerInterface::ID => $trigger->getId(),
                    '_query'             => [
                        'campaigns' => [$trigger->getCampaignId()],
                    ],
                ]
            ),
            'view_url'      => $this->urlBuilder->getUrl(
                'email/campaign/view',
                [
                    CampaignInterface::ID => $trigger->getCampaignId(),
                    '_fragment'           => TriggerInterface::ID . '_' . $trigger->getId(),
                ]
            ),
            'delete_url'    => $this->urlBuilder->getUrl(
                'email/trigger/delete',
                [TriggerInterface::ID => $trigger->getId()]
            ),
            'toggle_url'    => $this->urlBuilder->getUrl(
                'email/trigger/toggle',
                [TriggerInterface::ID => $trigger->getId()]
            ),

            'report' => [
                'pendingCount' => $this->countPendingEmails($trigger->getId()),
            ],
        ];

        $data = array_merge_recursive($data, $extraData);

        $data = $this->addChains($data);

        return $data;
    }

    private function addChains($data)
    {
        $data['chains'] = [];

        $collection = $this->chainRepository->getCollection();
        $collection->addFieldToFilter(ChainInterface::TRIGGER_ID, $data[TriggerInterface::ID]);

        foreach ($collection as $chain) {
            $chainData = [
                'id_field_name'          => ChainInterface::ID,
                ChainInterface::ID       => $chain->getId(),
                TemplateInterface::TITLE => $chain->getTemplate()
                    ? $chain->getTemplate()->getTitle()
                    : __('No Template Selected'),
                'info'                   => (string)$chain->toString(),
            ];

            foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
                $chainData = $modifier->modifyData($chainData);
            }

            $data['chains'][] = $chainData;
        }

        return $data;
    }

    /**
     *
     * @param TriggerInterface $trigger
     * @return string
     */
    private function getRule(TriggerInterface $trigger)
    {
        /** @var Rule $rule */
        $rule = $this->ruleFactory->create();
        $rule->loadPost($trigger->getRule());

        return $rule->toString();
    }

    /**
     * @param TriggerInterface $trigger
     * @return string
     */
    private function getEvent(TriggerInterface $trigger)
    {
        if (!$trigger->getEvent()) {
            return '';
        }

        return (string)$this->eventRepository->getInstance($trigger->getEvent())
            ->getEvents()[$trigger->getEvent()];
    }

    /**
     * @param int $triggerId
     * @return int
     */
    private function countPendingEmails($triggerId)
    {
        $queues = $this->queueRepository->getCollection();
        $queues->addFieldToFilter(TriggerInterface::ID, $triggerId)
            ->addFieldToFilter(QueueInterface::STATUS, QueueInterface::STATUS_PENDING);

        return $queues->count();
    }
}

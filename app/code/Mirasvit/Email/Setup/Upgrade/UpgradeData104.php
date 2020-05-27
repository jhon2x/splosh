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
use Mirasvit\Email\Api\Repository\Trigger\ChainRepositoryInterface;
use Mirasvit\Email\Model\ResourceModel\Trigger\Chain\Collection as ChainCollection;
use Mirasvit\Email\Model\Trigger\Chain;
use Mirasvit\Email\Helper\Serializer;

class UpgradeData104 implements UpgradeDataInterface, VersionableInterface
{
    const VERSION = '1.0.4';

    /**
     * @var ChainRepositoryInterface
     */
    private $chainRepository;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        ChainRepositoryInterface $chainRepository,
        Serializer               $serializer
    ) {
        $this->chainRepository = $chainRepository;
        $this->serializer      = $serializer;

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
        $this->doUpgrade();
    }

    /**
     * We removed an ability to send the emails every X period and at specific time of day.
     */
    private function doUpgrade()
    {
        /** @var ChainCollection $chainCollection */
        $chainCollection = $this->chainRepository->getCollection()->loadData();
        /** @var Chain $chain */
        foreach ($chainCollection as $chain) {
            $delay = $this->getSerializedChainData($chain);

            if ($delay->getRange() == 'year') {
                $days = $delay->getFrequency() * 365;
            } elseif ($delay->getRange() == 'month') {
                $days = $delay->getFrequency() * 30;
            } elseif ($delay->getRange() == 'week') {
                $days = $delay->getFrequency() * 7;
            } else {
                $days = $delay->getFrequency();
            }

            $excludeDays = $delay->getExcludeDays();
            if (!is_array($excludeDays)) {
                $excludeDays = [];
            }

            $chain->setDay($days)
                ->setHour($delay->getHours())
                ->setMinute($delay->getMinutes())
                ->setExcludeDays(implode(',', $excludeDays));

            // update send_from - send_to
            if ($chain->getType() == 'at') {
                if ($chain->getHour() > 0) {
                    $chain->setSendFrom($chain->getHour() - 1);
                } else {
                    $chain->setSendFrom(0);
                }

                $chain->setSendTo(($chain->getHour() + 2) % 24);
            }

            $chain->save();
        }
    }

    /**
     * Unserialize chain delay.
     *
     * @return \Magento\Framework\DataObject()
     */
    private function getSerializedChainData(Chain $chain)
    {
        $dataObject = new \Magento\Framework\DataObject();
        if ($this->serializer->unserialize($chain->getDelay())) {
            return $dataObject;
        }

        foreach ($this->serializer->unserialize($chain->getDelay()) as $key => $value) {
            $dataObject->setData($key, $value);
        }

        return $dataObject;
    }
}

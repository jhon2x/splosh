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

/**
 * In this update we introduced ability to send emails every X period.
 * So we should update delay for earlier versions to make them compatible.
 */
class UpgradeData102 implements UpgradeDataInterface, VersionableInterface
{
    const VERSION = '1.0.2';

    /**
     * @var ChainRepositoryInterface
     */
    private $chainRepository;

    public function __construct(
        ChainRepositoryInterface $chainRepository
    ) {
        $this->chainRepository = $chainRepository;
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
        /** @var ChainCollection $chainCollection */
        $chainCollection = $this->chainRepository->getCollection()->loadData();
        /** @var Chain $chain */
        foreach ($chainCollection as $chain) {
            if (!$chain->getFrequencyType()) {
                $chain->setFrequencyType('at')
                    ->setScheduleType($chain->getType())
                    ->setRange('day')
                    ->setFrequency($chain->getData('days') / 24 / 60 / 60)
                    ->setHours($chain->getData('hours') / 60 / 60)
                    ->setMinutes($chain->getData('minutes') / 60)
                    ->save();
            }
        }
    }
}

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
use Mirasvit\Email\Api\Repository\CampaignRepositoryInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Email\Helper\Data;

class UpgradeData110 implements UpgradeDataInterface, VersionableInterface
{
    const VERSION = '1.1.0';

    /**
     * @var TriggerRepositoryInterface
     */
    private $triggerRepository;
    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    public function __construct(
        Data $helper,
        TriggerRepositoryInterface $triggerRepository,
        CampaignRepositoryInterface $campaignRepository
    ) {
        $this->triggerRepository = $triggerRepository;
        $this->campaignRepository = $campaignRepository;
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
        $this->createCampaigns();
    }

    /**
     * Create campaigns based on existing triggers.
     */
    private function createCampaigns()
    {
        foreach ($this->triggerRepository->getCollection() as $trigger) {
            // create campaign
            $campaign = $this->campaignRepository->create();
            $campaign->setTitle('Campaign: ' . $trigger->getTitle())
                ->setDescription($trigger->getDescription())
                ->setIsActive($trigger->getIsActive());
            $this->campaignRepository->save($campaign);

            // connect with trigger
            $trigger->setCampaignId($campaign->getId());
            $this->triggerRepository->save($trigger);
        }
    }
}

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



namespace Mirasvit\Email\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Repository\CampaignRepositoryInterface;
use Mirasvit\Email\Api\Data\CampaignInterfaceFactory;
use Mirasvit\Email\Model\ResourceModel\Campaign\CollectionFactory;

class CampaignRepository implements CampaignRepositoryInterface
{
    /**
     * @var CampaignInterface[]
     */
    private $campaignRegistry = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CampaignInterfaceFactory
     */
    private $modelFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        EntityManager $entityManager,
        CampaignInterfaceFactory $modelFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->entityManager = $entityManager;
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->modelFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (isset($this->campaignRegistry[$id])) {
            return $this->campaignRegistry[$id];
        }

        $campaign = $this->create();
        $campaign = $this->entityManager->load($campaign, $id);

        if ($campaign->getId()) {
            $this->campaignRegistry[$id] = $campaign;
        } else {
            return false;
        }

        return $campaign;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CampaignInterface $model)
    {
        $this->entityManager->save($model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CampaignInterface $model)
    {
        return $this->entityManager->delete($model);
    }
}

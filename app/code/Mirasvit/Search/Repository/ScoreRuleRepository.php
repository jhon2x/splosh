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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Repository;

use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Api\Repository\ScoreRuleRepositoryInterface;
use Mirasvit\Search\Api\Data\ScoreRuleInterfaceFactory;
use Mirasvit\Search\Model\ResourceModel\ScoreRule\CollectionFactory;

class ScoreRuleRepository implements ScoreRuleRepositoryInterface
{
    /**
     * @var ScoreRuleInterfaceFactory
     */
    private $factory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        ScoreRuleInterfaceFactory $factory,
        CollectionFactory $collectionFactory
    ) {
        $this->factory = $factory;
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
        return $this->factory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        /** @var \Mirasvit\Search\Model\ScoreRule $model */
        $model = $this->create();
        $model->load($id);

        if (!$model->getId()) {
            return false;
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ScoreRuleInterface $model)
    {
        /** @var \Mirasvit\Search\Model\ScoreRule $model */
        $model->delete();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ScoreRuleInterface $model)
    {
        /** @var \Mirasvit\Search\Model\ScoreRule $model */
        $model->save();

        return $this;
    }
}

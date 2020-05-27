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


namespace Mirasvit\Search\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\Search\Api\Service\IndexServiceInterface;
use Mirasvit\Search\Model\Config;

class FullReindexPlugin
{
    /**
     * @var IndexRepositoryInterface
     */
    protected $indexRepository;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        IndexRepositoryInterface $indexRepository,
        Config $config
    ) {
        $this->indexRepository = $indexRepository;
        $this->config = $config;
    }

    /**
     * @param Fulltext $fulltext
     * @param \Closure $proceed
     * @param null     $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecuteFull(
        Fulltext $fulltext,
        \Closure $proceed,
        $scope = null
    ) {
        $result = $proceed($scope);
        foreach ($this->indexRepository->getCollection() as $index) {
            if ($index->getIsActive()) {
                if ($index->getIdentifier() == 'catalogsearch_fulltext') {
                    $index->setStatus(IndexInterface::STATUS_READY);
                    $this->indexRepository->save($index);
                } else {
                    $this->indexRepository->getInstance($index)->reindexAll();
                }
            }
        }

        return $result;
    }
}

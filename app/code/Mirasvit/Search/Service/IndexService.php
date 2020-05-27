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


namespace Mirasvit\Search\Service;

use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\Search\Api\Service\IndexServiceInterface;
use Mirasvit\Search\Api\Data\IndexInterface;

class IndexService implements IndexServiceInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    protected $indexRepository;

    public function __construct(
        IndexRepositoryInterface $indexRepository
    ) {
        $this->indexRepository = $indexRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCollection(IndexInterface $index)
    {
        return $this->indexRepository->getInstance($index)->getSearchCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryResponse(IndexInterface $index)
    {
        return $this->indexRepository->getInstance($index)->getQueryResponse();
    }
}

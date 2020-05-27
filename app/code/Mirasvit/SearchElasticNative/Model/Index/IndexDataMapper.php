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



namespace Mirasvit\SearchElasticNative\Model\Index;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;

class IndexDataMapper implements BatchDataMapperInterface 
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @param IndexRepositoryInterface $indexRepository
     */
    public function __construct(IndexRepositoryInterface $indexRepository) 
    {
        $this->indexRepository = $indexRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function map(array $documentData, $storeId, array $context = []) 
    {
        $indexIdentifier = $context['entityType'];
        $instance = $this->indexRepository->getInstance($indexIdentifier);

        $documents = [];

        foreach ($documentData as $entityId => $indexData) {
            $data = [];
            foreach ($indexData as $attrId => $value) {
                $attributeCode = $instance->getAttributeCode($attrId);
                $data[$attributeCode] = $value;
            }
            $documents[$entityId] = $data; 
        }
        return $documents;
    }

}
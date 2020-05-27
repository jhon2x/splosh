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



namespace Mirasvit\SearchElasticNative\Plugin;

use Magento\Framework\Registry;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;

class DataMapperResolverPlugin 
{
    /**
     * @var Magento\Framework\Registry
     */
    private $registry;

    function __construct(Registry $registry) 
    {
        $this->registry = $registry;
    }

    /**
     * Map index data for using in search engine metadata
     *
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function beforeMap(BatchDataMapperInterface $subject, array $documentData, $storeId, array $context = []) 
    {
        $entityType = $this->registry->registry('indexer_id');
        if ($entityType) {
            $context['entityType'] = $entityType;
        }
        return [$documentData, $storeId, $context];
    }

}
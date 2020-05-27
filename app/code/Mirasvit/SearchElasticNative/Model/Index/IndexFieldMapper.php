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

use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;

class IndexFieldMapper implements FieldMapperInterface 
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
    * {@inheritdoc}
    */
   public function getFieldName($attributeCode, $context = []) 
   {
       // not used as attribute code == field name
       throw new \Exception('Not implemented');
   }

    /**
     * {@inheritdoc}
     */
   public function getAllAttributesTypes($context = []) 
   {
        $indexId = $context['entityType'];
        $index = $this->indexRepository->get($indexId);
        $indexAttributes = array_keys($index->getAttributes());
        $attributeTypes = [];
        foreach ($indexAttributes as $attr) {
            $attributeTypes[$attr] = ['type' => 'text'];
        }
        return $attributeTypes;
   }
   
}
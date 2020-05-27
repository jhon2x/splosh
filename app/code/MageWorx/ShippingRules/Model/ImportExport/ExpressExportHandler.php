<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\ImportExport;

use MageWorx\ShippingRules\Api\Data\CarrierInterface;
use MageWorx\ShippingRules\Api\Data\MethodInterface;
use MageWorx\ShippingRules\Api\Data\RateInterface;
use MageWorx\ShippingRules\Api\ExportHandlerInterface;

/**
 * Class ExpressExportHandler
 */
class ExpressExportHandler extends AbstractImportExport implements ExportHandlerInterface
{
    /**
     * @var \MageWorx\ShippingRules\Model\ResourceModel\Rate\ExportCollectionFactory
     */
    private $exportRateCollectionFactory;

    /**
     * ExpressExportHandler constructor.
     *
     * @param \Magento\Framework\Reflection\MethodsMap $reflectionMethodsMap
     * @param \MageWorx\ShippingRules\Api\CarrierRepositoryInterface $carrierRepository
     * @param \MageWorx\ShippingRules\Api\MethodRepositoryInterface $methodRepository
     * @param \MageWorx\ShippingRules\Api\RateRepositoryInterface $rateRepository
     * @param \MageWorx\ShippingRules\Helper\Data $helper
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\DataObject\Factory $dataObjectFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \MageWorx\ShippingRules\Model\ResourceModel\Rate\ExportCollectionFactory $exportRateCollectionFactory
     * @param array $entities
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Reflection\MethodsMap $reflectionMethodsMap,
        \MageWorx\ShippingRules\Api\CarrierRepositoryInterface $carrierRepository,
        \MageWorx\ShippingRules\Api\MethodRepositoryInterface $methodRepository,
        \MageWorx\ShippingRules\Api\RateRepositoryInterface $rateRepository,
        \MageWorx\ShippingRules\Helper\Data $helper,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\DataObject\Factory $dataObjectFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \MageWorx\ShippingRules\Model\ResourceModel\Rate\ExportCollectionFactory $exportRateCollectionFactory,
        array $entities = [],
        array $data = []
    ) {
        parent::__construct(
            $reflectionMethodsMap,
            $carrierRepository,
            $methodRepository,
            $rateRepository,
            $helper,
            $searchCriteriaBuilder,
            $dataObjectFactory,
            $storeManager,
            $csvProcessor,
            $objectManager,
            $messageManager,
            $eventManager,
            $entities,
            $data
        );
        $this->exportRateCollectionFactory = $exportRateCollectionFactory;
    }

    /**
     * Get content as a CSV string
     *
     * @param array $entities - list of available entities
     * @param array $ids - format: ['entity1_code' => [id1,id2,...], ...]
     * @return string
     */
    public function getContent($entities = [], $ids = [])
    {
        if (empty($entities)) {
            $entities = $this->getAllAvailableEntitiesName();
        }

        $headers  = $this->getHeaders($entities);
        $template = $this->createStringCsvTemplate($headers);
        // Add header (titles)
        $content[] = $headers->toString($template);

        foreach ($entities as $entity) {
            $getEntityDataMethodName = 'get' . ucfirst($entity) . 's';
            if (!method_exists($this, $getEntityDataMethodName)) {
                continue;
            }

            $entityIds = empty($ids[$entity]) ? [] : $ids[$entity];
            $data      = $this->$getEntityDataMethodName($entityIds);

            foreach ($data as $datum) {
                if ($datum instanceof \Magento\Framework\DataObject) {
                    $datum->addData(
                        [
                            'ie_type' => $entity,
                            'id'      => $datum->getId()
                        ]
                    );
                    $content[] = $datum->toString($template);
                } else {
                    $datum['ie_type'] = $entity;
                    $datum['id']      = array_values($datum)[0];
                    $dataObject       = $this->dataObjectFactory->create($datum);
                    $content[]        = $dataObject->toString($template);
                }
            }
        }

        $contentAsAString = implode("\n", $content);

        return $contentAsAString;
    }

    /**
     * Create data template from headers
     *
     * @param \Magento\Framework\DataObject $headers
     * @return string
     */
    private function createStringCsvTemplate(\Magento\Framework\DataObject $headers)
    {
        $data         = $headers->getData();
        $templateData = [];
        foreach ($data as $propertyKey => $value) {
            $templateData[] = '"{{' . $propertyKey . '}}"';
        }
        $template = implode(',', $templateData);

        return $template;
    }

    /**
     * @param array $ids
     * @return CarrierInterface[]
     */
    private function getCarriers($ids = [])
    {
        if (empty($this->carriers)) {
            if (!empty($ids)) {
                $this->searchCriteriaBuilder->addFilter(
                    CarrierInterface::ENTITY_ID_FIELD_NAME,
                    $ids,
                    'in'
                );
            }
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $this->carriers = $this->carrierRepository
                ->getList($searchCriteria, true)
                ->getItems();
        }

        return $this->carriers;
    }

    /**
     * @param array $ids
     * @return MethodInterface[]
     */
    private function getMethods($ids = [])
    {
        if (empty($this->methods)) {
            if (!empty($ids)) {
                $this->searchCriteriaBuilder->addFilter(
                    MethodInterface::ENTITY_ID_FIELD_NAME,
                    $ids,
                    'in'
                );
            }
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $this->methods  = $this->methodRepository
                ->getList($searchCriteria, true)
                ->getItems();
        }

        return $this->methods;
    }

    /**
     * @param array $ids
     * @return RateInterface[]
     */
    private function getRates($ids = [])
    {
        if (empty($this->rates)) {
            /** @var \MageWorx\ShippingRules\Model\ResourceModel\Rate\ExportCollection $ratesCollection */
            $ratesCollection = $this->exportRateCollectionFactory->create();
            if (!empty($ids)) {
                $ratesCollection->addFieldToFilter($ratesCollection->getIdFieldName(), ['in' => $ids]);
            }

            $ratesCollection->joinLinkedTables();
            $sql = $ratesCollection->getSelectSql(true);
//            $rates = $ratesCollection->getItems();
            $rates = $ratesCollection->getConnection()->fetchAll($ratesCollection->getSelect());

            $this->rates = $rates;
        }

        return $this->rates;
    }
}
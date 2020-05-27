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



namespace Mirasvit\Search\Ui\Stopword\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Search\Api\Data\StopwordInterface;
use Mirasvit\Search\Api\Repository\StopwordRepositoryInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var StopwordRepositoryInterface
     */
    private $stopwordRepository;

    public function __construct(
        StopwordRepositoryInterface $repository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->stopwordRepository = $repository;
        $this->collection = $this->stopwordRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $result = [];

        foreach ($this->stopwordRepository->getCollection() as $stopword) {
            $data = [
                StopwordInterface::ID       => $stopword->getId(),
                StopwordInterface::TERM     => $stopword->getTerm(),
                StopwordInterface::STORE_ID => $stopword->getStoreId(),
            ];
            $result[$stopword->getId()] = $data;
        }

        return $result;
    }
}

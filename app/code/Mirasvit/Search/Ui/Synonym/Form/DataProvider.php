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



namespace Mirasvit\Search\Ui\Synonym\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Search\Api\Data\SynonymInterface;
use Mirasvit\Search\Api\Repository\SynonymRepositoryInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var SynonymRepositoryInterface
     */
    private $synonymRepository;

    public function __construct(
        SynonymRepositoryInterface $repository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->synonymRepository = $repository;
        $this->collection = $this->synonymRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $result = [];

        foreach ($this->synonymRepository->getCollection() as $synonym) {
            $data = [
                SynonymInterface::ID       => $synonym->getId(),
                SynonymInterface::TERM     => $synonym->getTerm(),
                SynonymInterface::SYNONYMS => $synonym->getSynonyms(),
                SynonymInterface::STORE_ID => $synonym->getStoreId(),
            ];
            $result[$synonym->getId()] = $data;
        }

        return $result;
    }
}

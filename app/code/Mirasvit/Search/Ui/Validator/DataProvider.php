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



namespace Mirasvit\Search\Ui\Validator;

use Magento\Framework\Data\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Search\Model\Config;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Config $config,
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->config     = $config;
        $this->collection = $collectionFactory->create();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [
            'items' => [
                [
                    'id'            => 0,
                    'id_field_name' => 0,
                    'limit'         => $this->config->getResultsLimit(),
                    'engine'        => $this->config->getEngine(),
                ],
            ],
        ];

        return $data;
    }
}

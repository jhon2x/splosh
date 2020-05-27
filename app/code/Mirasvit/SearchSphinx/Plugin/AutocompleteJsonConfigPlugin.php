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
 * @package   mirasvit/module-search-sphinx
 * @version   1.1.49
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchSphinx\Plugin;

use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\SearchSphinx\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

class AutocompleteJsonConfigPlugin
{
    private $config;

    private $indexRepository;

    private $storeManager;

    public function __construct(
        Config $config,
        IndexRepositoryInterface $indexRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->indexRepository = $indexRepository;
        $this->storeManager = $storeManager;
    }

    public function afterGenerate($subject, $config)
    {
        if ($config['engine'] !== 'sphinx') {
            return $config;
        }

        $config = array_merge($config, $this->getEngineConfig());
        foreach ($this->storeManager->getStores() as $store) {
            foreach ($config['indexes'][$store->getId()] as $i => $data) {
                $identifier = $data['identifier'];
                $data = array_merge($data, $this->getEngineIndexConfig($identifier, $store->getId()));
                $config['indexes'][$store->getId()][$i] = $data;
            }
        }

        return $config;
    }

    /**
     * @param string $identifier
     * @param $dimension
     * @return array
     */
    public function getEngineIndexConfig($identifier, $dimension)
    {
        $index = $this->indexRepository->get($identifier);

        $instance = $this->indexRepository->getInstance($index);
        $indexName = $instance->getIndexer()->getIndexName($dimension);

        $result = [];
        $result['index'] = $indexName;
        $result['fields'] = $instance->getAttributeWeights();

        return $result;
    }

    /**
     * @return array
     */
    public function getEngineConfig()
    {
        return [
            'host'      => $this->config->getHost(),
            'port'      => $this->config->getPort(),
            'available' => true,
        ];
    }
}

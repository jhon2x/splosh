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
 * @package   mirasvit/module-search-autocomplete
 * @version   1.1.96
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\SearchAutocomplete\Repository;

use Mirasvit\SearchAutocomplete\Api\Data\Index\InstanceInterface;
use Mirasvit\SearchAutocomplete\Api\Data\IndexProviderInterface;
use Mirasvit\SearchAutocomplete\Api\Repository\IndexRepositoryInterface;

class IndexRepository implements IndexRepositoryInterface
{
    /**
     * @var IndexProviderInterface
     */
    private $indexProvider;

    /**
     * @var InstanceInterface[]
     */
    private $instances;

    public function __construct(
        $indexProviders = [],
        $additionalProviders = [],
        array $instances = []
    ) {
        if ($additionalProviders) {
            $this->indexProvider = current($additionalProviders);
        } else {
            $this->indexProvider = current($indexProviders);
        }

        $this->instances = $instances;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndices()
    {
        return $this->indexProvider->getIndices();
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection($index)
    {
        return $this->indexProvider->getCollection($index);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryResponse($index)
    {
        return $this->indexProvider->getQueryResponse($index);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($identifier)
    {
        foreach ($this->instances as $id => $instance) {
            if ($identifier == $id) {
                return $instance;
            }
        }

        return false;
    }
}

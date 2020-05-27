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



namespace Mirasvit\Search\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;

class Index implements ArrayInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    public function __construct(
        IndexRepositoryInterface $indexRepository
    ) {
        $this->indexRepository = $indexRepository;
    }

    /**
     * @param bool $onlyUnused
     * @return array
     */
    public function toOptionArray($onlyUnused = false)
    {
        $options = [];
        foreach ($this->indexRepository->getList() as $instance) {
            $identifier = $instance->getIdentifier();
            if (!$onlyUnused
                || !$this->indexRepository->getCollection()
                    ->getItemByColumnValue(IndexInterface::IDENTIFIER, $identifier)
            ) {
                $options[] = [
                    'label' => (string)$instance,
                    'value' => $identifier,
                ];
            }
        }

        return $options;
    }
}

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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\Search\Model\Config;

class IndexTree implements ArrayInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        IndexRepositoryInterface $indexRepository,
        RequestInterface $request
    ) {
        $this->indexRepository = $indexRepository;
        $this->request         = $request;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        $identifiers = $this->indexRepository->getCollection()
            ->getColumnValues('identifier');

        $current = $this->indexRepository->get($this->request->getParam(IndexInterface::ID));

        foreach ($this->indexRepository->getList() as $instance) {
            if (in_array($instance->getIdentifier(), $identifiers)
                && in_array($instance->getIdentifier(), Config::DISALLOWED_MULTIPLE)
                && (!$current || $current->getIdentifier() != $instance->getIdentifier())) {
                continue;
            }

            $identifier = $instance->getIdentifier();
            $group      = trim(explode('/', $instance->getName())[0]);
            $name       = trim(explode('/', $instance->getName())[1]);

            if (!isset($options[$group])) {
                $options[$group] = [
                    'label'    => $group,
                    'value'    => $group,
                    'optgroup' => [],
                ];
            }

            $options[$group]['optgroup'][] = [
                'label' => (string)$name,
                'value' => $identifier,
            ];
        }

        return array_values($options);
    }
}

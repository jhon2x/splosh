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
 * @package   mirasvit/module-search-report
 * @version   1.0.5
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchReport\Plugin;

use Magento\Framework\Registry;

class SearchPlugin
{
    const REGISTRY_KEY = 'QueryTotalCount';

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSearch($subject, $result)
    {
        /** @var \Magento\Framework\Api\Search\SearchResult $result */

        $this->registry->register(self::REGISTRY_KEY, $result->getTotalCount(), true);

        return $result;
    }
}
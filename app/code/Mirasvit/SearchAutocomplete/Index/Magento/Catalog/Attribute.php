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



namespace Mirasvit\SearchAutocomplete\Index\Magento\Catalog;

use Mirasvit\SearchAutocomplete\Index\AbstractIndex;
use Magento\Framework\UrlFactory;

class Attribute extends AbstractIndex
{
    /**
     * @var UrlFactory
     */
    private $urlFactory;

    public function __construct(
        UrlFactory $urlFactory
    ) {
        $this->urlFactory = $urlFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];

        $attr = $this->index->getData('properties/attribute');

        foreach ($this->getCollection() as $value) {
            $url = $this->urlFactory->create()
                ->getUrl('catalogsearch/advanced/result', ['_query' => [$attr => $value->getValue()]]);

            $items[] = [
                'name' => $value->getLabel(),
                'url'  => $url,
            ];
        }

        return $items;
    }
}

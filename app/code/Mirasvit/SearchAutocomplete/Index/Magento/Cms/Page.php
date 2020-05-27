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



namespace Mirasvit\SearchAutocomplete\Index\Magento\Cms;

use Magento\Cms\Helper\Page as PageHelper;
use Mirasvit\SearchAutocomplete\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

class Page extends AbstractIndex
{
    /**
     * @var PageHelper
     */
    private $pageHelper;

    public function __construct(
        PageHelper $pageHelper
    ) {
        $this->pageHelper = $pageHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];

        /** @var \Magento\Cms\Model\Page $page */
        foreach ($this->getCollection() as $page) {
            $items[] = $this->mapPage($page);
        }

        return $items;
    }

    /**
     * @param \Magento\Cms\Model\Page $page
     * @return array
     */
    public function mapPage($page)
    {
        return [
            'name' => $page->getTitle(),
            'url'  => $this->pageHelper->getPageUrl($page->getIdentifier()),
        ];
    }

    public function map($data)
    {
        foreach ($data as $entityId => $itm) {
            $om = ObjectManager::getInstance();
            $entity = $om->create('Magento\Cms\Model\Page')->load($entityId);

            $map = $this->mapPage($entity);
            $data[$entityId]['autocomplete'] = $map;
        }

        return $data;
    }
}

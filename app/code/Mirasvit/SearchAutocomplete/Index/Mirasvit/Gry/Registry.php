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


namespace Mirasvit\SearchAutocomplete\Index\Mirasvit\Gry;

use Mirasvit\SearchAutocomplete\Index\AbstractIndex;

class Registry extends AbstractIndex
{
    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->collection->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];

        /** @var \Mirasvit\Giftr\Model\Registry $registry */
        foreach ($this->getCollection() as $registry) {
            $items[] = [
                'title' => $registry->getName(),
                'url'   => $registry->getViewUrl(),
                'name'  => __('Registrant: %1', $registry->getRegistrantAndCoName()),
            ];
        }

        return $items;
    }
}

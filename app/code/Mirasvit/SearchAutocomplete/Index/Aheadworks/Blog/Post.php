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



namespace Mirasvit\SearchAutocomplete\Index\Aheadworks\Blog;

use Mirasvit\SearchAutocomplete\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

class Post extends AbstractIndex
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Url
     */
    private $url;

    public function __construct()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

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
        $url = $this->objectManager->create('Aheadworks\Blog\Model\Url');
        foreach ($this->getCollection() as $post) {

            $items[] = [
                'name' => $post->getTitle(),
                'url'  => $url->getPostUrl($post),
            ];
        }

        return $items;
    }
}

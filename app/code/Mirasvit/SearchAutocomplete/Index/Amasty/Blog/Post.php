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



namespace Mirasvit\SearchAutocomplete\Index\Amasty\Blog;

use Mirasvit\SearchAutocomplete\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

class Post extends AbstractIndex
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

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

        /** @var \Amasty\Blog\Model\Posts $post */
        foreach ($this->getCollection() as $post) {
            $items[] = $this->mapPost($post);
        }

        return $items;
    }

    /**
     * @param \Amasty\Blog\Model\Posts $post
     * @return array
     */
    public function mapPost($post)
    {
        return [
            'name' => $post->getTitle(),
            'url'  => $post->getPostUrl(),
        ];
    }

    public function map($data)
    {
        foreach ($data as $entityId => $itm) {
            $om = ObjectManager::getInstance();
            $entity = $om->create('Amasty\Blog\Model\Posts')->load($entityId);

            $map = $this->mapPost($entity);
            $data[$entityId]['autocomplete'] = $map;
        }

        return $data;
    }
}
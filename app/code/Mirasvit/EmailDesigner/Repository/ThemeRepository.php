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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailDesigner\Repository;


use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\EmailDesigner\Api\Data\ThemeInterface;
use Mirasvit\EmailDesigner\Api\Data\ThemeInterfaceFactory;
use Mirasvit\EmailDesigner\Api\Repository\ThemeRepositoryInterface;
use Mirasvit\EmailDesigner\Model\ResourceModel\Theme\CollectionFactory;

class ThemeRepository implements ThemeRepositoryInterface
{
    /**
     * @var ThemeInterface[]
     */
    private $themeRegistry = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ThemeInterfaceFactory
     */
    private $modelFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * ThemeRepository constructor.
     *
     * @param EntityManager         $entityManager
     * @param ThemeInterfaceFactory $modelFactory
     * @param CollectionFactory     $collectionFactory
     */
    public function __construct(
        EntityManager $entityManager,
        ThemeInterfaceFactory $modelFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->entityManager = $entityManager;
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->modelFactory->create();
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        if (isset($this->themeRegistry[$id])) {
            return $this->themeRegistry[$id];
        }

        $theme = $this->create();
        $theme = $this->entityManager->load($theme, $id);

        if ($theme->getId()) {
            $this->themeRegistry[$id] = $theme;
        } else {
            return false;
        }

        return $theme;
    }

    /**
     * {@inheritDoc}
     */
    public function save(ThemeInterface $theme)
    {
        return $this->entityManager->save($theme);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(ThemeInterface $theme)
    {
        return $this->entityManager->delete($theme);
    }
}

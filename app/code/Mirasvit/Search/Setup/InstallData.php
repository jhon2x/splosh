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



namespace Mirasvit\Search\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    protected $indexRepository;

    public function __construct(
        IndexRepositoryInterface $indexRepository
    ) {
        $this->indexRepository = $indexRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $categoryIndex = $this->indexRepository->create()
            ->setIdentifier('magento_catalog_category')
            ->setTitle('Categories')
            ->setIsActive(0)
            ->setPosition(2)
            ->setAttributes([
                'name'             => 10,
                'description'      => 5,
                'meta_title'       => 9,
                'meta_keywords'    => 1,
                'meta_description' => 1,
            ]);
        $this->indexRepository->save($categoryIndex);

        $pageIndex = $this->indexRepository->create()
            ->setIdentifier('magento_cms_page')
            ->setTitle('Information')
            ->setIsActive(0)
            ->setPosition(3)
            ->setAttributes([
                'title'            => 10,
                'content'          => 5,
                'content_heading'  => 9,
                'meta_keywords'    => 1,
                'meta_description' => 1,
            ]);
        $this->indexRepository->save($pageIndex);

        $setup->endSetup();
    }
}

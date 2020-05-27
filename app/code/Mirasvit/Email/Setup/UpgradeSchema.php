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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Setup;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\Email\Setup\Upgrade\VersionableInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var File
     */
    private $file;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        File $file
    ) {
        $this->file = $file;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        foreach ($this->getUpgradeSchemas() as $upgradeSchema) {
            if (version_compare($context->getVersion(), $upgradeSchema->getVersion(), '<')) {
                $upgradeSchema->upgrade($setup, $context);
            }
        }

        $setup->endSetup();
    }

    /**
     * Get available UpgradeSchema class instances.
     *
     * @return UpgradeSchemaInterface[]|VersionableInterface[]
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getUpgradeSchemas()
    {
        $upgradeSchemas = [];
        $upgradeScriptPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Upgrade';

        foreach ($this->file->readDirectory($upgradeScriptPath) as $file) {
            if (strpos($file, 'UpgradeSchema') !== false && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $class = __NAMESPACE__ . '\Upgrade\\' . str_replace('.php', '', basename($file));
                $upgradeSchema = $this->objectManager->create($class);
                if ($upgradeSchema instanceof UpgradeSchemaInterface
                    && $upgradeSchema instanceof VersionableInterface
                ) {
                    $upgradeSchemas[] = $upgradeSchema;
                }
            }
        }

        return $upgradeSchemas;
    }
}

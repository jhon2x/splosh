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
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Mirasvit\Email\Setup\Upgrade\VersionableInterface;

class UpgradeData implements UpgradeDataInterface
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
     * {@inheritDoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        foreach ($this->getUpgradeData() as $upgradeData) {
            if ($context->getVersion() && version_compare($context->getVersion(), $upgradeData->getVersion(), '<')) {
                $upgradeData->upgrade($setup, $context);
            }
        }

        $setup->endSetup();
    }

    /**
     * Get available UpgradeData class instances.
     *
     * @return UpgradeDataInterface[]|VersionableInterface[]
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getUpgradeData()
    {
        $upgradeDataObjects = [];
        $upgradeScriptPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Upgrade';

        foreach ($this->file->readDirectory($upgradeScriptPath) as $file) {
            if (strpos($file, 'UpgradeData') !== false && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $dataClass = __NAMESPACE__ . '\Upgrade\\' . str_replace('.php', '', basename($file));
                $upgradeData = $this->objectManager->create($dataClass);
                if ($upgradeData instanceof UpgradeDataInterface
                    && $upgradeData instanceof VersionableInterface
                ) {
                    $upgradeDataObjects[] = $upgradeData;
                }
            }
        }

        return $upgradeDataObjects;
    }
}

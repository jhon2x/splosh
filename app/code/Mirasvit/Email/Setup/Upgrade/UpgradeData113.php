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



namespace Mirasvit\Email\Setup\Upgrade;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Mirasvit\Email\Api\Repository\Trigger\ChainRepositoryInterface;
use Mirasvit\EmailDesigner\Model\Template;

class UpgradeData113 implements UpgradeDataInterface, VersionableInterface
{
    const VERSION = '1.1.3';
    /**
     * @var ChainRepositoryInterface
     */
    private $chainRepository;

    public function __construct(ChainRepositoryInterface $chainRepository)
    {
        $this->chainRepository = $chainRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * {@inheritDoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->reformatTemplateIds();
    }

    /**
     * We added ability to use native Magento templates for triggers.
     * So we should update all triggers' chains for new format of template_id identifier.
     */
    private function reformatTemplateIds()
    {
        foreach ($this->chainRepository->getCollection() as $chain) {
            if (strpos($chain->getTemplateId(), Template::TYPE) === false) {
                $chain->setTemplateId(Template::TYPE . ':' . $chain->getTemplateId());
                $this->chainRepository->save($chain);
            }
        }
    }
}

<?php

namespace Acidgreen\Customer\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;

    /**
    * @param \Magento\Cms\Model\BlockFactory $blockFactory
    */
    public function __construct(
        \Magento\Cms\Model\BlockFactory $blockFactory
    ) {
        $this->_blockFactory = $blockFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
      	$setup->startSetup();

      	if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $block = $this->_blockFactory->create();
            $block->setTitle('B2C Login Disclaimer')
                ->setIdentifier('b2c_login_disclaimer')
                ->setIsActive(true)
                ->setStores(array(0))
                ->setContent('If you are not a stockist, please login to the Customer Portal')
                ->save();
      	}

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $block = $this->_blockFactory->create();
            $block->setTitle('B2B Announcement')
                ->setIdentifier('b2b_announcement')
                ->setIsActive(true)
                ->setStores(array(0))
                ->setContent('')
                ->save();
      	}

        $setup->endSetup();
    }
}

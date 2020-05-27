<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_BannersLite
 */


namespace Amasty\BannersLite\Model\ResourceModel;

use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class BannerRule extends AbstractDb
{
    const TABLE_NAME = 'amasty_banners_lite_rule';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, BannerRuleInterface::ENTITY_ID);
    }
}

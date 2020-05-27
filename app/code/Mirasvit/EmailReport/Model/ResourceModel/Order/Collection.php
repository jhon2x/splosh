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
 * @package   mirasvit/module-email-report
 * @version   2.0.8
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailReport\Model\ResourceModel\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mirasvit\EmailReport\Model\ResourceModel\CollectionTrait;

class Collection extends AbstractCollection
{
    use CollectionTrait;

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Mirasvit\EmailReport\Model\Order::class,
            \Mirasvit\EmailReport\Model\ResourceModel\Order::class
        );
    }
}

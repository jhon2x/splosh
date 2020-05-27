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



namespace Mirasvit\EmailReport\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\EmailReport\Api\Data\OrderInterface;

class Order extends AbstractModel implements OrderInterface
{
    use ReportProperties;

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Order::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * {@inheritDoc}
     */
    public function setAmount($value)
    {
        $this->setData(self::AMOUNT, $value);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setParentId($parentId)
    {
        $this->setData(self::PARENT_ID, $parentId);

        return $this;
    }
}

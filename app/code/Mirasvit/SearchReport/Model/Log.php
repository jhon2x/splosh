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
 * @package   mirasvit/module-search-report
 * @version   1.0.5
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchReport\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\SearchReport\Api\Data\LogInterface;

class Log extends AbstractModel implements LogInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\SearchReport\Model\ResourceModel\Log');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(LogInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->getData(LogInterface::QUERY);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery($value)
    {
        return $this->setData(LogInterface::QUERY, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackQuery()
    {
        return $this->getData(LogInterface::FALLBACK_QUERY);
    }

    /**
     * {@inheritdoc}
     */
    public function setFallbackQuery($value)
    {
        return $this->setData(LogInterface::FALLBACK_QUERY, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMisspellQuery()
    {
        return $this->getData(LogInterface::MISSPELL_QUERY);
    }

    /**
     * {@inheritdoc}
     */
    public function setMisspellQuery($value)
    {
        return $this->setData(LogInterface::MISSPELL_QUERY, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        return $this->getData(LogInterface::RESULTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setResults($value)
    {
        return $this->setData(LogInterface::RESULTS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIp()
    {
        return $this->getData(LogInterface::IP);
    }

    /**
     * {@inheritdoc}
     */
    public function setIp($value)
    {
        return $this->setData(LogInterface::IP, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSession()
    {
        return $this->getData(LogInterface::SESSION);
    }

    /**
     * {@inheritdoc}
     */
    public function setSession($value)
    {
        return $this->setData(LogInterface::SESSION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(LogInterface::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($value)
    {
        return $this->setData(LogInterface::CUSTOMER_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountry()
    {
        return $this->getData(LogInterface::COUNTRY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCountry($value)
    {
        return $this->setData(LogInterface::COUNTRY, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderItemId()
    {
        return $this->getData(LogInterface::ORDER_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderItemId($value)
    {
        return $this->setData(LogInterface::ORDER_ITEM_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getClicks()
    {
        return $this->getData(LogInterface::CLICKS);
    }

    /**
     * {@inheritdoc}
     */
    public function setClicks($value)
    {
        return $this->setData(LogInterface::CLICKS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return $this->getData(LogInterface::SOURCE);
    }

    /**
     * {@inheritdoc}
     */
    public function setSource($value)
    {
        return $this->setData(LogInterface::SOURCE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(LogInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($value)
    {
        return $this->setData(LogInterface::CREATED_AT, $value);
    }
}
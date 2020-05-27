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
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Event\Api\Data\EventInterface;

class Event extends AbstractModel implements EventInterface
{
    const PARAMS = 'params';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Event\Model\ResourceModel\Event');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($value)
    {
        return $this->setData(self::ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentifier($value)
    {
        return $this->setData(self::IDENTIFIER, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->getData(self::KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setKey($value)
    {
        return $this->setData(self::KEY, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getParamsSerialized()
    {
        return $this->getData(self::PARAMS_SERIALIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function setParamsSerialized($value)
    {
        return $this->setData(self::PARAMS_SERIALIZED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getParams()
    {
        return $this->loadParams()
            ->getData(self::PARAMS);
    }

    /**
     * {@inheritdoc}
     */
    public function setParams($value)
    {
        return $this->loadParams()
            ->setData(self::PARAMS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getParam($key)
    {
        return $this->loadParams()
            ->getData(self::PARAMS . '/' . $key);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($value)
    {
        return $this->setData(self::STORE_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($value)
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($value)
    {
        return $this->setData(self::UPDATED_AT, $value);
    }

    /**
     * @return $this
     */
    private function loadParams()
    {
        if (!$this->hasData(self::PARAMS)) {
            $data = $this->getParamsSerialized()
                ? \Zend_Json_Decoder::decode($this->getParamsSerialized())
                : [];
            $this->setData(self::PARAMS, $data);
        }

        return $this;
    }
}
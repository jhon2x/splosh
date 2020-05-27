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



namespace Mirasvit\Search\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Mirasvit\Search\Api\Data\IndexInterface;
use Magento\Framework\Model\AbstractModel;

class Index extends AbstractModel implements IndexInterface
{
    /**
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->_init('Mirasvit\Search\Model\ResourceModel\Index');

        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return parent::getData(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($input)
    {
        return parent::setData(self::TITLE, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return parent::getData(self::IDENTIFIER);
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentifier($input)
    {
        return parent::setData(self::IDENTIFIER, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return parent::getData(self::POSITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition($input)
    {
        return parent::setData(self::POSITION, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        $data = unserialize(parent::getData(self::ATTRIBUTES_SERIALIZED));

        if (is_array($data)) {
            return $data;
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes($input)
    {
        return parent::setData(self::ATTRIBUTES_SERIALIZED, serialize($input));
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        $data = unserialize(parent::getData(self::PROPERTIES_SERIALIZED));

        if (is_array($data)) {
            return $data;
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setProperties($input)
    {
        return parent::setData(self::PROPERTIES_SERIALIZED, serialize($input));
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($input)
    {
        return parent::setData(self::STATUS, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return parent::getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($input)
    {
        return parent::setData(self::IS_ACTIVE, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty($key)
    {
        $props = $this->getProperties();

        if (isset($props[$key])) {
            return $props[$key];
        }

        return false;
    }
}

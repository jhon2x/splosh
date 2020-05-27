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



namespace Mirasvit\Event\EventData;


use Magento\Framework\DataObject;
use Mirasvit\Event\Api\Data\EventDataInterface;

class DataWrapper extends DataObject implements EventDataInterface
{
    /** Event Data Fields */
    const IDENTIFIER      = 'identifier';
    const LABEL           = 'label';
    const CONDITION_CLASS = 'condition_class';
    const ATTRIBUTES      = 'attributes';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionClass()
    {
        return $this->getData(self::CONDITION_CLASS);
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
    public function getAttributes()
    {
        return $this->getData(self::ATTRIBUTES);
    }
}
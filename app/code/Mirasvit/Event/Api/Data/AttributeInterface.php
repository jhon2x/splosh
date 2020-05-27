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



namespace Mirasvit\Event\Api\Data;


use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;

interface AttributeInterface
{
    const CODE            = 'code';
    const LABEL           = 'label';
    const TYPE            = 'type';
    const OPTIONS         = 'options';
    const CONDITION_CLASS = 'condition_class';

    /**
     * Get attribute code.
     *
     * @return string
     */
    public function getCode();

    /**
     * Get attribute label.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Get attribute options.
     *
     * @return array
     */
    public function getOptions();

    /**
     * Get attribute type.
     *
     * @return string
     */
    public function getType();

    /**
     * Get attribute value for given model.
     *
     * @param AbstractModel $dataObject
     *
     * @return mixed
     */
    public function getValue(AbstractModel $dataObject);

    /**
     * Get attribute condition class.
     *
     * @return string
     */
    public function getConditionClass();
}

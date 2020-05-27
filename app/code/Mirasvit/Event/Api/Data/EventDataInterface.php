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

interface EventDataInterface
{
    const ATTRIBUTE_TYPE_STRING = 'string';
    const ATTRIBUTE_TYPE_ENUM = 'enum';
    const ATTRIBUTE_TYPE_ENUM_MULTI = 'multi_enum';
    const ATTRIBUTE_TYPE_NUMBER = 'number';
    const ATTRIBUTE_TYPE_BOOL = 'bool';
    const ATTRIBUTE_TYPE_DATE = 'date';

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return string
     */
    public function getConditionClass();

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return array|AttributeInterface[]
     */
    public function getAttributes();

    /**
     * Object data getter
     *
     * If $key is not defined will return all the data as an array.
     * Otherwise it will return value of the element specified by $key.
     * It is possible to use keys like a/b/c for access nested array data
     *
     * If $index is specified it will assume that attribute data is an array
     * and retrieve corresponding member. If data is the string - it will be explode
     * by new line character and converted to array.
     *
     * @param string     $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key = '', $index = null);
}
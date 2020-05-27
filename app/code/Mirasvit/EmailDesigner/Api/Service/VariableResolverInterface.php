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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailDesigner\Api\Service;


use Magento\Framework\DataObject;

interface VariableResolverInterface
{
    /**
     * Resolve host object of the method with given $name.
     *
     * Invoke method on resolved variable host object and return its result.
     *
     * @param  string $name - method name
     * @param  array  $args - method arguments
     *
     * @return mixed
     */
    public function resolve($name, $args = []);

    /**
     * Add new host object of variables.
     *
     * @param object|array $variable
     *
     * @return $this
     */
    public function addVariable($variable);

    /**
     * Retrieve variable host objects.
     *
     * @param bool $isAll - define whether return all variables or not
     *
     * @return VariableInterface[]
     */
    public function getVariables($isAll = false);

    /**
     * Retrieve all original variables.
     *
     * @return VariableInterface[]
     */
    public function getOrigVariables();

    /**
     * Get variable objects that host variables for given $object.
     *
     * @param object|string $object - object or class name
     *
     * @return VariableInterface[]
     */
    public function getVariablesFor($object);

    /**
     * Get variable object by its namespace.
     *
     * @param string $namespace
     *
     * @return VariableInterface
     */
    public function getVariable($namespace);

    /**
     * Set variable resolver context.
     *
     * @param DataObject $context
     *
     * @return $this
     */
    public function setContext(DataObject $context);
}

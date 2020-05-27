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

interface VariableInterface
{
    /**
     * Docblock tag used as the variable name.
     */
    const DOCBLOCK_TAG_DESCRIPTION = 'desc';

    /**
     * Docblock tag used as the variable namespace.
     */
    const DOCBLOCK_TAG_NAMESPACE = 'namespace';

    /**
     * Docblock tag used as the variable filter.
     */
    const DOCBLOCK_TAG_FILTER = 'filter';

    /**
     * Docblock tag used to highlight a return type.
     */
    const DOCBLOCK_TAG_RETURN = 'return';

    /**
     * Key used to identify callback.
     */
    const CALLBACK = 'callback';

    /**
     * @param DataObject $context
     *
     * @retunr $this
     */
    public function setContext(DataObject $context);

    /**
     * Get namespace for variable class.
     *
     * @return string
     */
    public function getNamespace();

    /**
     * Get name for variable class.
     *
     * @return string
     */
    public function getVariableName();

    /**
     * Check whether the variable can be served as a host of methods for given $object.
     *
     * @param object $object
     *
     * @return bool
     */
    public function isFor($object);

    /**
     * Get list of allowed methods by variable.
     *
     * @return string[]
     */
    public function getWhitelist();

    /**
     * Return array contained variable callback and return type of that callback
     * or false if a callback does not exist:
     *
     * ['return' => ClassName, 'callback' => [$this, 'methodName']]
     *
     * @return array
     */
    public function getCallback();

    /**
     * Variable classes should be invokable.
     */
    public function __invoke();
}

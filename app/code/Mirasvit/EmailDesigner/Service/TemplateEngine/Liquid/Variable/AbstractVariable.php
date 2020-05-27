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



namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable;


use Magento\Framework\DataObject;
use Mirasvit\EmailDesigner\Api\Service\VariableInterface;

abstract class AbstractVariable implements VariableInterface
{
    protected $supportedTypes = [];

    /**
     * List of allowed methods.
     *
     * @var string[]
     */
    protected $whitelist = [];

    /**
     * @var DataObject
     */
    protected $context;

    /**
     * Callback cache.
     */
    protected $callback = [];

    public function __construct()
    {
        $this->context = new DataObject();
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(DataObject $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Last word in a lowercase from the class name is used as a variable namespace.
     *
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return strtolower(substr(static::class, strrpos(static::class, '\\') + 1));
    }

    /**
     * {@inheritdoc}
     */
    public function getVariableName()
    {
        return $this->getNamespace();
    }

    /**
     * {@inheritdoc}
     */
    public function isFor($object)
    {
        foreach ($this->supportedTypes as $class) {
            if ($object instanceof $class) {
                return true;
            }

            if (is_string($object) && ltrim($object, '\\') == ltrim($class, '\\')) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelist()
    {
        return $this->whitelist;
    }

    /**
     * {@inheritdoc}
     */
    public function getCallback()
    {
        if (isset($this->callback[$this->getNamespace()])) {
            return $this->callback[$this->getNamespace()];
        }

        $methodName = 'get'.ucfirst($this->getNamespace());
        if (method_exists($this, $methodName)) {
            $method = new \Zend_Reflection_Method($this, $methodName);
            $return = $method->getDocblock()->getTag(self::DOCBLOCK_TAG_RETURN)->getType();
            $this->callback[$this->getNamespace()] = [
                self::DOCBLOCK_TAG_RETURN => $return,
                self::CALLBACK => [$this, $methodName]
            ];

            return $this->callback[$this->getNamespace()];
        } else {
            return false;
        }
    }

    /**
     * Each variable class may implement method with name equal to its namespace.
     * And may return the main object associated with this variable.
     * If no method exists, $this is returned.
     *
     * {@inheritdoc}
     */
    public function __invoke()
    {
        if ($callback = $this->getCallback()) {
            if ($result = call_user_func($callback[self::CALLBACK])) {
                return $result;
            }
        }

        return $this;
    }
}

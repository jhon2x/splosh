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


namespace Mirasvit\EmailDesigner\Service;

use Magento\Framework\DataObject;
use Mirasvit\EmailDesigner\Api\Service\VariableInterface;
use Mirasvit\EmailDesigner\Api\Service\VariableResolverInterface;

class VariableResolver implements VariableResolverInterface
{
    /**
     * List of variable hosts.
     *
     * @var object
     */
    protected $variables = [];

    /**
     * @var DataObject
     */
    protected $context;

    /**
     * List of variable hosts.
     *
     * @var VariableInterface[]
     */
    private $origVariables;

    public function __construct($variables = [])
    {
        $this->origVariables = $variables;
        $this->variables = $variables;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($name, $args = [])
    {
        $result = false;
        $methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        foreach ($this->getVariables(true) as $variable) {
            if ($variable instanceof VariableInterface) { // set variable context
                $variable->setContext($this->context);
            }

            // invoke method with given name
            if ($this->canInvoke($variable, $name, $args)) {
                return $this->invoke($variable, $name, $args);
            }

            // invoke method name prepended with the "get" keyword
            if ($this->canInvoke($variable, $methodName, $args)) {
                return $this->invoke($variable, $methodName, $args);
            }

            // check existence of a data with key $name in the data object
            if ($variable instanceof DataObject && $variable->hasData($name)) {
                return $variable->getData($name);
            }

            // invoke object itself
            /*if ($variable instanceof VariableInterface && $name == $variable->getNamespace()) {
                return $variable();
            }*/
        }

        return $result;
    }

    /**
     * We cannot invoke $variable's method if number of passed $args less than number of required args.
     *
     * @param object $variable - possible host object of invoked method
     * @param string $name     - method name
     * @param array  $args     - method arguments
     *
     * @return bool
     */
    private function canInvoke($variable, $name, array $args = [])
    {
        if (!method_exists($variable, $name)) {
            return false;
        }

        $reflectionMethod = new \ReflectionMethod($variable, $name);
        if (count($args) < $reflectionMethod->getNumberOfRequiredParameters()
            || count($args) > $reflectionMethod->getNumberOfParameters()
        ) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariables($isAll = false)
    {
        foreach ($this->variables as $key => $variable) {
            // if a variable is a callback - then execute the callback and store its result as a variable
            // where lazy loading actually happens
            if (is_array($variable) && is_callable($variable)) {
                $result = call_user_func($variable);
                if (is_object($result)) {
                    $this->variables[get_class($result)] = $result;
                } else {
                    unset($this->variables[$key]);
                }
            }
        }

        if ($isAll) {
            return $this->variables;
        }

        $variables = [];
        foreach ($this->variables as $variable) {
            if ($variable instanceof VariableInterface) {
                $variables[$variable->getNamespace()] = $variable;
            }
        }

        return $variables;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrigVariables()
    {
        return $this->origVariables;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariablesFor($object)
    {
        $variables = [];
        foreach ($this->origVariables as $variable) {
            if ($variable->isFor($object)) {
                $variables[] = $variable;
            }
        }

        return $variables;
    }

    /**
     * {@inheritdoc}
     */
    public function addVariable($variable)
    {
        if (!is_object($variable) && !(is_array($variable) && isset($variable[VariableInterface::CALLBACK]))) {
            throw new \InvalidArgumentException('"variable" argument should be an object or callable.');
        }

        if (is_array($variable) && array_key_exists(VariableInterface::CALLBACK, $variable)) {
            $class = $variable[VariableInterface::DOCBLOCK_TAG_RETURN];
            $variable = $variable[VariableInterface::CALLBACK];
        }

        // add new variable to the beginning
        array_unshift($this->variables, $variable);

        if (isset($class)) {
            $variable = $class;
        }

        // prepend variables associated with the newly added variable
        foreach ($this->getVariablesFor($variable) as $variableFor) {
            $this->addVariable($variableFor);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariable($namespace)
    {
        foreach ($this->origVariables as $variable) {
            if ($variable->getNamespace() === $namespace) {
                return $variable;
            }
        }

        return null;
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
     * Reset variables on cloning.
     */
    public function __clone()
    {
        $this->variables = [];
    }

    /**
     * Invoke method on a variable with given args.
     */
    private function invoke($variable, $methodName, $args)
    {
        $result = call_user_func_array([$variable, $methodName], $args); // maybe remove args

        if ($result instanceof \Magento\Framework\Phrase) {
            $result = $result->__toString();
        }

        return $result;
    }
}

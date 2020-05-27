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



namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid;


use Liquid\Drop;
use Mirasvit\EmailDesigner\Api\Service\VariableResolverInterface;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\Context as VariableContext;

class DropProxy extends Drop
{
    /**
     * @var object $variableResolver
     */
    private $variableResolver;

    /**
     * @var VariableContext $context
     */
    protected $variableContext;

    public function __construct(VariableContext $variableContext, VariableResolverInterface $variableResolver)
    {
        $this->variableContext = $variableContext;
        $this->variableResolver = $variableResolver;
        $this->variableResolver->setContext($this->variableContext);
    }

    /**
     * Retrieve value from variableResolver object.
     *
     * @param string $name      - method name
     * @param array  $arguments - method arguments
     *
     * @return DropProxy|mixed
     */
    public function __call($name, $arguments)
    {
        $value = $this->variableResolver->resolve($name);

        // add result variable to context
        $this->variableContext->setData($name, $value);

        $result = $this->prepareResult($value);

        return $result;
    }

    /**
     * Convert result object to DropProxy type.
     * If $result is not object return as is.
     *
     * @param mixed $result
     *
     * @return mixed|DropProxy
     */
    private function prepareResult($result)
    {
        if (is_object($result)) {
            $variableContext  = clone $this->variableContext;
            $variableResolver = clone $this->variableResolver;
            $variableResolver->addVariable($result);

            // push result object to variable context for later use in variable
            $this->addResultToContext($variableContext, $result);

            $result = new self($variableContext, $variableResolver);
        } elseif (is_array($result)) {
            // convert each object in the result array to DropProxy
            foreach ($result as $key => $item) {
                if (is_object($item)) {
                    $result[$key] = $this->prepareResult($item);
                }
            }
        }

        return $result;
    }

    /**
     * Add result object to variable context by key for later use.
     *
     * @param VariableContext $variableContext
     * @param object          $result
     */
    private function addResultToContext(VariableContext $variableContext, $result)
    {
        if (method_exists($result, 'getEventObject')) {
            $variableContext->setData($result->getEventObject(), $result);
        } elseif ($result instanceof \Magento\Framework\Model\AbstractModel) {
            $eventPrefix = explode('_', $result->getEventPrefix());
            $variableContext->setData(array_pop($eventPrefix), $result);
        }
    }
}

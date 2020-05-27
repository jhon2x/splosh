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


use Mirasvit\EmailDesigner\Api\Service\VariableInterface;
use Mirasvit\EmailDesigner\Api\Service\VariableProviderInterface;
use Mirasvit\EmailDesigner\Api\Service\VariableResolverInterface;

class VariableProvider implements VariableProviderInterface
{
    /**
     * @var VariableResolverInterface
     */
    private $variableResolver;

    public function __construct(
        VariableResolverInterface $variableResolver
    ) {
        $this->variableResolver = $variableResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariables()
    {
        $variables = $this->variableResolver->getOrigVariables();
        $result = [
            [
                'label' => __('General Variables'),
                'value' => [
                    [
                        'label' => __('Customer Name'),
                        'value' => __('{{ customer_name }}')
                    ],
                    [
                        'label' => __('Customer Email'),
                        'value' => __('{{ customer_email }}')
                    ],
                    [
                        'label' => __('Customer First Name'),
                        'value' => __('{{ customer_name | split: " " | first }}')
                    ],
                ]
            ]
        ];

        foreach ($variables as $variable) {
            $variableReflection = new \Zend_Reflection_Class($variable);
            /** @var \Zend_Reflection_Method[] $reflectionMethods */
            $reflectionMethods = $variableReflection->getMethods(\ReflectionMethod::IS_PUBLIC);

            if ($methods = $this->collectMethods($variable, $reflectionMethods)) {
                $result[] = [
                    'label' => __('%1 Variables', ucfirst($variable->getVariableName())),
                    'value' => $methods
                ];
            }
        }

        return $result;
    }

    /**
     * Collect all available methods for user.
     *
     * @param VariableInterface         $variable
     * @param \Zend_Reflection_Method[] $reflectionMethods
     *
     * @return array
     * @throws \Zend_Reflection_Exception
     */
    private function collectMethods(VariableInterface $variable, array $reflectionMethods = [])
    {
        $methods = [];
        foreach ($reflectionMethods as $method) {
            if ($this->canUseMethod($method, $variable)) {
                $docblock = $method->getDocblock();
                $methodName = $this->convertToLiquid($method->getName());

                // add methods available in the model object
                if ($this->isObjectReturned($docblock) && $variable->getNamespace() == $methodName) {
                    $returnType = $docblock->getTag('return')->getType();
                    $variableReflection = new \Zend_Reflection_Class($returnType);
                    $reflectionMethods = $variableReflection->getMethods(\ReflectionMethod::IS_PUBLIC);

                    $methods = array_merge(
                        $methods,
                        $this->collectMethods($variable, $reflectionMethods)
                    );

                    continue;
                }

                if ($definition = $this->getMethodDefinition($variable, $method)) {
                    array_unshift($methods, $definition);
                }
            }
        }

        return $methods;
    }

    /**
     * Determine whether the method can be used or not.
     *
     * @param \Zend_Reflection_Method $method
     * @param VariableInterface       $variable
     *
     * @return bool
     * @throws \Zend_Reflection_Exception
     */
    private function canUseMethod(\Zend_Reflection_Method $method, VariableInterface $variable)
    {
        $canUse = false;
        if (strpos($method->getName(), 'get') === 0 // only getters
            && strpos($method->getDeclaringClass()->getName(), 'AbstractVariable') === false // ignore AbstractClass
            && strpos($method->getDocComment(), '@Suppress') === false // tags without space throw an error
            && $method->getNumberOfRequiredParameters() === 0 // only methods without parameters
            && stripos($method->getDocComment(), '@inheritdoc') === false // ignore methods with "inheritdoc"
            && $method->getDocComment() // skip methods without docblock
        ) {
            $canUse = true;
        }

        return $canUse;
    }

    /**
     * Convert method name to liquid compatible syntax.
     *
     * e.g., "getCouponCode" => coupon_code
     *
     * @param string $methodName
     *
     * @return string
     */
    private function convertToLiquid($methodName)
    {
        $methodWords = preg_split('/(?=[A-Z])/', $methodName); // split name by uppercase letters
        $methodWords = array_filter($methodWords, function($name) { // remove keyword "get" from words
            return $name !== 'get';
        });

        // join remained words with underscore
        return implode('_', array_map('strtolower', $methodWords));
    }

    /**
     * Whether the method return type is object or not.
     *
     * @param \Zend_Reflection_Docblock $docblock
     *
     * @return bool
     *
     */
    private function isObjectReturned(\Zend_Reflection_Docblock $docblock)
    {
        if ($docblock->hasTag('return')) {
            return strpos($docblock->getTag('return')->getType(), '\\') !== false;
        }

        return false;
    }

    /**
     * Get liquid variable definition.
     *
     * @param VariableInterface $variable
     * @param \Zend_Reflection_Method $method
     *
     * @return array
     * @throws \Zend_Reflection_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getMethodDefinition(VariableInterface $variable, \Zend_Reflection_Method $method)
    {
        $definition = [];
        $docblock   = $method->getDocblock();
        $methodName = $this->convertToLiquid($method->getName());
        $namespace  = $docblock->getTag(VariableInterface::DOCBLOCK_TAG_NAMESPACE)
            ? $docblock->getTag(VariableInterface::DOCBLOCK_TAG_NAMESPACE)->getDescription()
            : $variable->getNamespace();

        if ((method_exists($variable, $method->getName())
                && $method->getDeclaringClass()->getName() === get_class($variable)
            ) || in_array($method->getName(), $variable->getWhitelist())
        ) {
            $value = '';
            if ($docblock->getTag('return')->getType() == 'array'
                || strpos($docblock->getTag('return')->getType(), '[]') !== false
            ) {
                $value .= "{% for item in {$namespace}.{$methodName} %}";
                $value .= "\\n\\n";
                $value .= "{% endfor %}";
            } else {
                $filter = $docblock->getTag(VariableInterface::DOCBLOCK_TAG_FILTER)
                    ? $docblock->getTag(VariableInterface::DOCBLOCK_TAG_FILTER)->getDescription()
                    : null;

                $value = "{{ {$namespace}.{$methodName} ";

                if ($filter !== null) {
                    $value .= $filter; // add filter to variable
                }

                $value .= "}}"; // close variable
            }

            $definition = [
                'value' => $value,
                'label' => $docblock->hasTag(VariableInterface::DOCBLOCK_TAG_DESCRIPTION)
                    ? $docblock->getTag(VariableInterface::DOCBLOCK_TAG_DESCRIPTION)->getDescription()
                    : explode("\n", $docblock->getShortDescription())[0]
            ];
        }

        return $definition;
    }
}
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



namespace Mirasvit\EmailDesigner\Service\TemplateEngine;


use Magento\Framework\ObjectManagerInterface;
use Mirasvit\EmailDesigner\Api\Service\TemplateEngineInterface;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\Context;
use Mirasvit\EmailDesigner\Api\Service\VariableResolverInterface;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\DropProxy;
use Liquid\Template as LiquidTemplate;
use Liquid\Liquid as LiquidLib;

class Liquid implements TemplateEngineInterface
{
    /**
     * Ignore directives used by magento template engine to avoid conflict.
     *
     * @var string[]
     */
    private $ignoredDirectives = [
        'template',
        'depend',
        '\/depend',
        'var',
        'if',
        '\/if',
        'block',
        'layout',
        'view',
        'media',
        'store',
        'trans',
        'protocol',
        'config',
        'customvar',
        'css',
        'inlinecss',
    ];

    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var object[]
     */
    private $filterObjects = [];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Liquid constructor.
     *
     * @param Context                   $context
     * @param VariableResolverInterface $variableResolver
     * @param ObjectManagerInterface    $objectManager
     * @param array                     $filters
     */
    public function __construct(
        Context $context,
        VariableResolverInterface $variableResolver,
        ObjectManagerInterface $objectManager,
        array $filters = []
    ) {
        $this->variableResolver = $variableResolver;
        $this->context = $context;
        $this->filters = $filters;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, array $variables = [])
    {
        $this->context->unsetData();
        $this->context->addData($variables);

        // create drop variables for liquid
        $variables['this'] = new DropProxy($this->context, $this->variableResolver);
        foreach ($this->variableResolver->getOrigVariables() as $variable) {
            $variable->setContext($this->context);
            $variableResolver = clone $this->variableResolver;
            if ($variable->getCallback()) { // implement lazy loading
                $variableResolver->addVariable($variable->getCallback());
            } else {
                $variableResolver->addVariable($variable);
            }

            $variables[$variable->getNamespace()] = new DropProxy($this->context, $variableResolver);
        }

        // change variable start regexp to exclude Magento Template Engine directives
        $variableStartRegexp = LiquidLib::get('VARIABLE_START') . '(?!' . implode('|', $this->ignoredDirectives) . ')';
        LiquidLib::set('VARIABLE_START', $variableStartRegexp);

        // process template
        $liquidTemplate = new LiquidTemplate();
        $liquidTemplate->parse($template);

        $result = $liquidTemplate->render($variables, $this->getFilters(), $this->context->getData());

        return $result;
    }

    /**
     * Get filter instances.
     *
     * @return object[]
     */
    private function getFilters()
    {
        if (empty($this->filterObjects)) {
            foreach ($this->filters as $filter) {
                $this->filterObjects[] = $this->objectManager->create($filter);
            }
        }

        return $this->filterObjects;
    }
}

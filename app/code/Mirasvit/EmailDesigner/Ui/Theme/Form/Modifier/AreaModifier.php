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



namespace Mirasvit\EmailDesigner\Ui\Theme\Form\Modifier;


use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\EmailDesigner\Api\Data\ThemeInterface;
use Mirasvit\EmailDesigner\Api\Repository\ThemeRepositoryInterface;
use Mirasvit\EmailDesigner\Api\Service\VariableProviderInterface;

class AreaModifier implements ModifierInterface
{
    /**
     * @var ThemeRepositoryInterface
     */
    private $themeRepository;
    /**
     * @var ArrayManager
     */
    private $arrayManager;
    /**
     * @var ContextInterface
     */
    private $context;
    /**
     * @var VariableProviderInterface
     */
    private $variableProvider;

    public function __construct(
        VariableProviderInterface $liquidVariableProvider,
        ThemeRepositoryInterface $themeRepository,
        ArrayManager $arrayManager,
        ContextInterface $context
    ) {
        $this->themeRepository = $themeRepository;
        $this->arrayManager = $arrayManager;
        $this->context = $context;
        $this->variableProvider = $liquidVariableProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $theme = $this->themeRepository->get($this->context->getRequestParam(ThemeInterface::ID, null));

        $meta = $this->arrayManager->set(
            'general/children/editor',
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'variables' => $this->getVariables()
                        ]
                    ]
                ],
                'children' => [
                    'areas' => [
                        'children' => $this->getAreaFields($theme ?: null)
                    ]
                ]
            ]
        );

        return $meta;
    }

    /**
     * Prepare area fields for theme.
     *
     * @param ThemeInterface $theme
     *
     * @return array
     */
    private function getAreaFields(ThemeInterface $theme = null)
    {
        return [
            ThemeInterface::TEMPLATE_TEXT => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'component' => 'Mirasvit_EmailDesigner/js/area',
                            'componentType' => 'textarea',
                            'dataType' => 'text',
                            'label' => 'Template Text',
                            'formElement' => 'textarea',
                            'source' => 'theme',
                            'dataScope' => ThemeInterface::TEMPLATE_TEXT,
                            'value' => $theme ? $theme->getTemplateText() : '',
                            'elementTmpl' => 'Mirasvit_EmailDesigner/editor/areas/area',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get theme variables.
     *
     * @return array
     */
    private function getVariables()
    {
        $variables = $this->variableProvider->getVariables();
        $variables = array_merge([
            [
                'label' => __('Theme Variables'),
                'value' => [
                    [
                        'label' => __('Create Editable Area'),
                        'value' => "{{ 'area_name' | area: 'Default area text' }}"
                    ]
                ]
            ]
        ], $variables);

        return $variables;
    }
}
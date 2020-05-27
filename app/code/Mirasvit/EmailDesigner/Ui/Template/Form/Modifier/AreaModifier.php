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



namespace Mirasvit\EmailDesigner\Ui\Template\Form\Modifier;


use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;
use Mirasvit\EmailDesigner\Api\Repository\TemplateRepositoryInterface;
use Mirasvit\EmailDesigner\Api\Service\VariableProviderInterface;

class AreaModifier implements ModifierInterface
{
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;
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
        TemplateRepositoryInterface $templateRepository,
        ArrayManager $arrayManager,
        ContextInterface $context
    ) {
        $this->templateRepository = $templateRepository;
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
        $id = $this->context->getRequestParam(TemplateInterface::ID, null);

        if ($id && ($template = $this->templateRepository->get($id))) {
            $meta = $this->arrayManager->set(
                'general/children/editor',
                $meta,
                [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'variables' => $this->variableProvider->getVariables()
                            ]
                        ]
                    ],
                    'children' => [
                        'areas' => [
                            'children' => $this->getAreaFields($template)
                        ]
                    ]
                ]
            );
        } else {
            // do not show editor for new template
            $meta = $this->arrayManager->set(
                'general/children/editor/arguments/data/config',
                $meta,
                [
                    'visible' => false
                ]
            );
        }

        return $meta;
    }

    /**
     * Prepare area fields for template.
     *
     * @param TemplateInterface $template
     *
     * @return array
     */
    private function getAreaFields(TemplateInterface $template)
    {
        $areaFields = [];
        foreach ($template->getAreas() as $code => $label) {
            $areaFields['area_' . $code] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'component' => 'Mirasvit_EmailDesigner/js/area',
                            'componentType' => 'textarea',
                            'dataType' => 'text',
                            'label' => $label,
                            'formElement' => 'textarea',
                            'source' => 'template',
                            'dataScope' => $code,
                            'value' => $template->getAreaText($code) ? $template->getAreaText($code) : '',
                            'elementTmpl' => 'Mirasvit_EmailDesigner/editor/areas/area',
                        ]
                    ]
                ]
            ];
        }

        return $areaFields;
    }
}
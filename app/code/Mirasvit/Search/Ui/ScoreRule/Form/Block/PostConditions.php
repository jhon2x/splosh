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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Ui\ScoreRule\Form\Block;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset as FieldsetRenderer;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Model\ScoreRule\Rule;

class PostConditions extends Generic implements TabInterface
{
    /**
     * @var FieldsetRenderer
     */
    private $fieldsetRenderer;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var PostConditionsRenderer
     */
    private $postConditionsRenderer;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var string
     */
    private $formName;

    public function __construct(
        PostConditionsRenderer $postConditionsRenderer,
        FieldsetRenderer $fieldsetRenderer,
        Context $context,
        Registry $registry,
        FormFactory $formFactory
    ) {
        $this->setNameInLayout('post_conditions');

        $this->postConditionsRenderer = $postConditionsRenderer;
        $this->fieldsetRenderer = $fieldsetRenderer;
        $this->formFactory = $formFactory;
        $this->registry = $registry;

        parent::__construct($context, $registry, $formFactory);
    }

    protected function _prepareForm()
    {
        $this->formName = Rule::FORM_NAME;

        /** @var ScoreRuleInterface $scoreRule */
        $scoreRule = $this->registry->registry(ScoreRuleInterface::class);
        $rule = $scoreRule->getRule();

        $form = $this->formFactory->create();

        $form->setHtmlIdPrefix('rule_');

        $renderer = $this->fieldsetRenderer
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setData('new_child_url', $this->getUrl('search/scoreRule/newPostConditionHtml', [
                'form'      => 'rule_post_conditions_fieldset',
                'form_name' => $this->formName,
            ]));

        $fieldset = $form->addFieldset(
            'post_conditions_fieldset',
            [
            'legend' => __('Apply the rule only when the following conditions are met:'),
            'class'  => 'fieldset',
            ]
        )->setRenderer($renderer);

        $rule->getConditions()->setFormName($this->formName);

        $conditionsField = $fieldset->addField('post_conditions', 'text', [
            'name'           => 'post_conditions',
            'required'       => true,
            'data-form-part' => $this->formName,
        ]);

        $conditionsField->setRule($rule)
            ->setRenderer($this->postConditionsRenderer)
            ->setFormName($this->formName);

        $this->setConditionFormName($rule->getPostConditions(), $this->formName);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param object $conditions
     * @param string $formName
     * @return void
     */
    private function setConditionFormName($conditions, $formName)
    {
        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}

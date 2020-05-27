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
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Ui\Event\Block;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset as FieldsetRenderer;
use Magento\Rule\Block\Conditions as ConditionsBlock;

class Rule extends Generic implements TabInterface
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
     * @var ConditionsBlock
     */
    private $conditionsBlock;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var string
     */
    private $formName;

    /**
     * @var \Mirasvit\Event\Model\Rule
     */
    private $rule;

    public function __construct(
        \Mirasvit\Event\Model\Rule $rule,
        ConditionsBlock $conditionsBlock,
        FieldsetRenderer $fieldsetRenderer,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->rule = $rule;
        $this->conditionsBlock = $conditionsBlock;
        $this->fieldsetRenderer = $fieldsetRenderer;
        $this->formFactory = $formFactory;
        $this->registry = $registry;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        if ($this->registry->registry('event_formName')) {
            $this->formName = $this->registry->registry('event_formName');
        } else {
            throw new \Exception("Register 'event_formName' wasn't defined");
        }

        if ($this->registry->registry('event_eventIdentifier')) {
            $eventIdentifier = $this->registry->registry('event_eventIdentifier');
        } else {
            return false;
        }

        if ($this->registry->registry('event_ruleConditions') !== false) {
            $ruleConditions = $this->registry->registry('event_ruleConditions');
        } else {
            throw new \Exception("Register 'event_ruleConditions' wasn't defined");
        }

        $rule = $this->rule;
        $rule->setEventIdentifier($eventIdentifier);

        if (is_array($ruleConditions)) {
            $rule->loadPost($ruleConditions);
        }

        $form = $this->formFactory->create();

        $form->setHtmlIdPrefix('rule_');
        $renderer = $this->fieldsetRenderer
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setData('new_child_url', $this->getUrl('event/rule/newConditionHtml', [
                'form'            => 'rule_conditions_fieldset',
                'form_name'       => $this->formName,
                'eventIdentifier' => $eventIdentifier,
            ]));

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend'    => __('Apply the rule only if the following conditions are met.'),
             'class' => 'fieldset event__rule',
            ]
        )->setRenderer($renderer);

        $rule->getConditions()->setFormName($this->formName);

        $conditionsField = $fieldset->addField('conditions', 'text', [
            'name'           => 'conditions',
            'required'       => true,
            'data-form-part' => $this->formName,
        ]);
        $conditionsField->setRule($rule)
            ->setRenderer($this->conditionsBlock)
            ->setFormName($this->formName);

        $this->setConditionFormName($rule->getConditions(), $this->formName, $eventIdentifier);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Handles addition of form name to condition and its conditions.
     *
     * @param object $conditions
     * @param string $formName
     * @param string $eventIdentifier
     * @return void
     */
    private function setConditionFormName($conditions, $formName, $eventIdentifier)
    {
        $conditions->setFormName($formName);
        $conditions->setEventIdentifier($eventIdentifier);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $eventIdentifier);
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

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->registry->registry('event_eventIdentifier')) {
            return __('Please select event first');
        }

        return parent::_toHtml();
    }
}
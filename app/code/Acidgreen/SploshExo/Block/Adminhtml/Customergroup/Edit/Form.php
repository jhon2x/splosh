<?php

namespace Acidgreen\SploshExo\Block\Adminhtml\Customergroup\Edit;

use Acidgreen\SploshExo\Model\SploshCustomerGroupFactory;
use Acidgreen\SploshExo\Model\Config\Source\MageCustomerGroups;

/**
 * Adminhtml customer groups edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Acidgreen\SploshExo\Model\SploshCustomerGroupFactory
     */
    protected $customerGroupFactory;

    /**
     * @var MageCustomerGroups
     */
    protected $mageCustomerGroups;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param SploshCustomerGroupFactory $customerGroupFactory
     * @param MageCustomerGroups $mageCustomerGroups
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        SploshCustomerGroupFactory $customerGroupFactory,
        MageCustomerGroups $mageCustomerGroups,
        array $data = []
    ) {
        $this->customerGroupFactory = $customerGroupFactory;
        $this->mageCustomerGroups = $mageCustomerGroups;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form for render
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        /** @var \Acidgreen\SploshExo\Model\SploshCustomerGroup $customerGroup */
        $customerGroup = $this->customerGroupFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Group Mapping Information')]);

        $fieldset->addField(
            'mage_group_id',
            'select',
            [
                'name' => 'mage_group',
                'label' => __('Magento Group'),
                'title' => __('Magento Group'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $this->mageCustomerGroups->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'exo_group_id',
            'text',
            [
                'name' => 'exo_group',
                'label' => __('EXO Group ID'),
                'title' => __('EXO Group ID'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $fieldset->addField(
            'description',
            'text',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'required' => true
            ]
        );


        if ($customerGroup->getId() !== null) {
            $form->addField('id', 'hidden', ['name' => 'id', 'value' => $customerGroup->getId()]);
        }

        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('acidgreen_sploshexo/*/save'));
        $form->setMethod('post');
        $this->setForm($form);
    }
}

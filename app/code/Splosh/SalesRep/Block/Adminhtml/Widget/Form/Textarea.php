<?php

namespace Splosh\SalesRep\Block\Adminhtml\Widget\Form;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;

class Textarea extends Template
{
    /**
     * @var ElementFactory
     */
    protected $elementFactory;

    /**
     * Textarea constructor.
     * @param ElementFactory $elementFactory
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        ElementFactory $elementFactory,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->elementFactory = $elementFactory;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $textarea = $this->elementFactory->create("textarea", ['data' => $element->getData()]);
        $textarea->setId($element->getId());
        $textarea->setForm($element->getForm());
        $textarea->setClass("widget-option input-textarea admin__control-text");

        if ($element->getRequired()) {
            $textarea->addClass('required-entry');
        }

        $element->setData('after_element_html', $textarea->getElementHtml());
        $element->setValue('');

        return $element;
    }
}
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
 * @package   mirasvit/module-email-report
 * @version   2.0.8
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\EmailReport\Block\Adminhtml\System\Config;

class Reset extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock  */
        $buttonBlock = $this->getForm()->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class);

        $data = [
            'label' => $this->getLabel(),
            'onclick' => "setLocation('" . $this->getResetUrl() . "')",
        ];

        return $buttonBlock->setData($data)->toHtml();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    private function getLabel()
    {
        return  __('Reset Statistic');
    }

    /**
     * @return string
     */
    private function getResetUrl()
    {
        return $this->getUrl('emailreport/report/reset');
    }
}

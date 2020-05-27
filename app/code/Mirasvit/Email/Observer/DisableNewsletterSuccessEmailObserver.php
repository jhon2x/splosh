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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class DisableNewsletterSuccessEmailObserver implements ObserverInterface
{
    /**
     * Add 'disable' option to Magento Newsletter's Success Email Template.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Config\Block\System\Config\Form $block */
        $block = $observer->getEvent()->getBlock();
        if (!$block
            || !$block instanceof \Magento\Config\Block\System\Config\Form
            || $block->getSectionCode() !== 'newsletter') {
            return;
        }

        foreach ($block->getForm()->getElements() as $element) {
            foreach ($element->getElements() as $elem) {
                if ($elem->getId() === 'newsletter_subscription_success_email_template') {
                    /** @var \Magento\Framework\Data\Form\Element\Select $elem */
                    $values = $elem->getValues();
                    $values[] = [
                        'value' => '',
                        'label' => __('Disabled'),
                    ];

                    $elem->setValues($values);
                }
            }
        }
    }
}

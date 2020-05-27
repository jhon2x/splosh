<?php

namespace Acidgreen\Customer\Block\Form\Login;

class Announcement extends \Magento\Framework\View\Element\Template
{
    /**
     * Returns Announcement Admin Block
     *
     * @return html
     */
    public function getAnnouncementBlock() {
        return $this->getLayout()->createBlock('Magento\Cms\Block\Block')
            ->setBlockId('b2b_announcement');
    }
}

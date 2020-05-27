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


namespace Mirasvit\Email\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

class Email extends GenericForm implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'Mirasvit_Email::customer/edit/tab/email.phtml';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @param Context                    $context
     * @param Registry                   $registry
     * @param FormFactory                $formFactory
     * @param AccountManagementInterface $customerAccountManagement
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        AccountManagementInterface $customerAccountManagement
    ) {
        $this->registry = $registry;
        $this->customerAccountManagement = $customerAccountManagement;
        parent::__construct($context, $registry, $formFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Follow Up Email');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Follow Up Email');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
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
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                'Mirasvit\Email\Block\Adminhtml\Customer\Edit\Tab\Email\Grid',
                'email.grid'
            )
        );
        parent::_prepareLayout();

        return $this;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}

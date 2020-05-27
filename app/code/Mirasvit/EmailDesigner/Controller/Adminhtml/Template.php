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



namespace Mirasvit\EmailDesigner\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;
use Mirasvit\EmailDesigner\Api\Repository\TemplateRepositoryInterface;

abstract class Template extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Mirasvit_EmailDesigner::email_designer_template';

    /**
     * @var TemplateRepositoryInterface
     */
    protected $templateRepository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Constructor
     *
     * @param TemplateRepositoryInterface $templateRepository
     * @param Registry                    $registry
     * @param Context                     $context
     */
    public function __construct(
        TemplateRepositoryInterface $templateRepository,
        Registry $registry,
        Context $context
    ) {
        $this->templateRepository = $templateRepository;
        $this->registry = $registry;
        $this->context = $context;
        $this->backendSession = $context->getSession();

        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magento_Backend::marketing');
        $resultPage->getConfig()->getTitle()->prepend(__('Email Designer'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Templates'));

        return $resultPage;
    }

    /**
     * Theme model
     *
     * @return \Mirasvit\EmailDesigner\Model\Template
     */
    public function initModel()
    {
        $model = $this->templateRepository->create();

        if ($this->getRequest()->getParam(TemplateInterface::ID)) {
            $model = $this->templateRepository->get($this->getRequest()->getParam(TemplateInterface::ID));
        }

        $this->registry->register('current_model', $model);

        return $model;
    }
}

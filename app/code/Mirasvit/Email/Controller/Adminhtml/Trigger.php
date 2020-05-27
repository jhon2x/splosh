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



namespace Mirasvit\Email\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\RepositoryInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Email\Controller\RegistryConstants;
use Mirasvit\Email\Model\TriggerFactory;

abstract class Trigger extends Action
{
    /**
     * Authorization level of a basic admin session for current page.
     */
    const ADMIN_RESOURCE = 'Mirasvit_Email::trigger';

    /**
     * @var TriggerFactory|RepositoryInterface
     */
    protected $triggerRepository;

    /**
     * @var Context
     */
    protected $context;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateTimeFilter;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\Filter\DateTime $dateTimeFilter,
        TriggerRepositoryInterface $triggerRepository,
        Registry $registry,
        Context $context
    ) {
        $this->dateTimeFilter = $dateTimeFilter;
        $this->triggerRepository = $triggerRepository;
        $this->registry = $registry;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magento_Backend::marketing');
        $resultPage->getConfig()->getTitle()->prepend(__('Follow Up Email'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Triggers'));

        return $resultPage;
    }

    /**
     * @return TriggerInterface
     */
    public function initModel()
    {
        $model = $this->triggerRepository->create();

        if ($this->getRequest()->getParam(TriggerInterface::ID)) {
            $model = $this->triggerRepository->get($this->getRequest()->getParam(TriggerInterface::ID));
            $this->registry->register(RegistryConstants::CURRENT_TRIGGER_ID, $model->getId());
            $this->registry->register(RegistryConstants::CURRENT_MODEL, $model);
        }

        return $model;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}

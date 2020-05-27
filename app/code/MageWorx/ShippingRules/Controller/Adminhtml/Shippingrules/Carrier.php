<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Controller\Adminhtml\Shippingrules;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use MageWorx\ShippingRules\Api\CarrierRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Carrier
 */
abstract class Carrier extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Date
     */
    protected $dateFilter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CarrierRepositoryInterface
     */
    protected $carrierRepository;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param Date $dateFilter
     * @param CarrierRepositoryInterface $carrierRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        Date $dateFilter,
        CarrierRepositoryInterface $carrierRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->coreRegistry      = $coreRegistry;
        $this->fileFactory       = $fileFactory;
        $this->dateFilter        = $dateFilter;
        $this->carrierRepository = $carrierRepository;
        $this->logger            = $logger;
    }

    /**
     * Initiate carrier
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _initCarrier()
    {
        if ($this->getRequest()->getParam('id')) {
            $carrier = $this->carrierRepository->getById($this->getRequest()->getParam('id'));
        } elseif ($this->getRequest()->getParam('carrier_id')) {
            $carrier = $this->carrierRepository->getById($this->getRequest()->getParam('carrier_id'));
        } elseif ($this->getRequest()->getParam('carrier_code')) {
            $carrier = $this->carrierRepository->getByCode($this->getRequest()->getParam('carrier_code'));
            $params = $this->getRequest()->getParams();
            $params['id'] = $carrier->getId();
            $this->getRequest()->setParams($params);
        } else {
            $carrier = $this->carrierRepository->getEmptyEntity();
        }

        $this->coreRegistry->register(
            \MageWorx\ShippingRules\Model\Carrier::CURRENT_CARRIER,
            $carrier
        );
    }

    /**
     * Initiate action
     *
     * @return Quote
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('MageWorx_ShippingRules::shippingrules_carrier')
             ->_addBreadcrumb(__('Carriers'), __('Carriers'));

        return $this;
    }

    /**
     * Returns result of current user permission check on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageWorx_ShippingRules::carrier');
    }
}

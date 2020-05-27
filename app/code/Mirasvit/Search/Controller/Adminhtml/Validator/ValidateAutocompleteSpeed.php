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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Controller\Adminhtml\Validator;

use Mirasvit\Search\Controller\Adminhtml\Validator;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;

use Magento\Framework\Controller\Result\JsonFactory;

class ValidateAutocompleteSpeed extends Validator
{
    protected $context;
    private $resultJsonFactory;

    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $response = $this->resultJsonFactory->create();
        $query = '';
        if ($this->getRequest()->getParam('q') && !empty($this->getRequest()->getParam('q'))) {
            $query = $this->getRequest()->getParam('q');
        } else {
            $status = self::STATUS_ERROR;
            $result = '<p>Please specify search term</p>';
            return $response->setData(['result' => $result,'status' => $status]);
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeManager->getStore()->getBaseUrl();
        $url = $storeManager->getStore()->getBaseUrl().'/searchautocomplete/ajax/suggest/?q='.$query ;

        $start = microtime(true);
        $reguest = curl_init();
        curl_setopt($reguest, CURLOPT_URL, $url);
        curl_setopt($reguest, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($reguest, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($reguest);
        curl_close($reguest);

        $result = round(microtime(true) - $start, 4);

        return $response->setData(['result' => $result . ' sec']);
    }
}

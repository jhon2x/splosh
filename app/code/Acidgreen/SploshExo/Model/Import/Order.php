<?php

namespace Acidgreen\SploshExo\Model\Import;

use Magento\Framework\Exception\LocalizedException;
use Acidgreen\Exo\Model\Import\AbstractImporter;
use Acidgreen\Exo\Model\ProcessFactory;
use Acidgreen\Exo\Helper\Data as HelperClass;
use Acidgreen\Exo\Helper\ImportError as ErrorHelper;
use Acidgreen\Exo\Helper\Api\Api as ApiHelper;
use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Framework\DB\Transaction;
use Psr\Log\LoggerInterface as Logger;
use Acidgreen\Exo\Helper\Order as OrderHelper;

class Order extends \Acidgreen\Exo\Model\Import\Order
{
    /**
    * Order Import Contructor
    *
    * @param ErrorHelper $errorHelper
    * @param ProcessFactory $processFactory
    * @param HelperClass $helper
    * @param ApiHelper $apiHelper
    * @param OrderCollectionFactory $orderCollectionFactory
    * @param InvoiceService $invoiceService
    * @param Transaction $transaction
    * @param Logger @logger
    */
  	public function __construct(
  		  ErrorHelper $errorHelper,
  		  ProcessFactory $processFactory,
  		  HelperClass $helper,
  		  ApiHelper $apiHelper,
  		  ConfigHelper $configHelper,
        OrderCollectionFactory $orderCollectionFactory,
        InvoiceService $invoiceService,
        ConvertOrder $convertOrder,
        Transaction $transaction,
  		  OrderHelper $orderHelper,
  	    Logger $logger
  	)
  	{
  		  parent::__construct(
            $errorHelper,
      		  $processFactory,
      		  $helper,
      		  $apiHelper,
      		  $configHelper,
            $orderCollectionFactory,
            $invoiceService,
            $convertOrder,
            $transaction,
      		  $orderHelper,
      	    $logger
        );
  	}

    protected function getExoOrderDetailsForUpdate($exoOrderId)
    {
        $orderDetailsForUpdate = array();

        $exoOrderResponse = $this->apiHelper->getExoOrderDetails($exoOrderId);

        if($exoOrderResponse['status'] == '200') {

            $body   = \GuzzleHttp\Ring\Core::body($exoOrderResponse);

            $exoOrderDetails = json_decode($body, true);


            $orderDetailsForUpdate = array(
                    'status_code' => $exoOrderDetails['status'],
                    'status_desc' => $exoOrderDetails['statusdescription'],
                );


                foreach($exoOrderDetails['lines'] as $exoOrderItems) {
                    if (!isset($exoOrderItems['stockcode'])) {
                        continue;
                    }
                    
                    $sku = $exoOrderItems['stockcode'];

                    $orderDetailsForUpdate['items'][$sku] = array(
                            'invoicedQty'   => $exoOrderItems['invoicedquantity'],
                            'shippedQty'    => $exoOrderItems['releasequantity'],
                            //'invoicedQty'   => 1,
                            //'shippedQty'    => 1,
                        );
                }



            $this->logger->debug(print_r($orderDetailsForUpdate, true));
        } else {
            $this->logger->debug(__('%1 :: ERROR fetching response. Response status = %2', __METHOD__, $exoOrderResponse['status']));
            $this->logger->debug(print_r($exoOrderResponse, true));
            // return an error array instead
            $exoErrorResponse = [
                'error_status' => $exoOrderResponse['status'],
                'response' => $exoOrderResponse
            ];
            return $exoErrorResponse;
        }

        return $orderDetailsForUpdate;
    }
}

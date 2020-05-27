<?php

namespace Splosh\SalesRep\Controller\Location;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Splosh\SalesRep\Model\Search\Result;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Splosh\SalesRep\Helper\Structure;

class Search extends Action
{
    /**
     * @var Result
     */
    protected $searchResult;

    /**
     * @var \Magento\Framework\Controller\Result\Raw
     */
    protected $resultRaw;

    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    protected $resultJson;

    /**
     * @var Structure
     */
    protected $structureHelper;

    /**
     * @var array
     */
    protected $detailsMapping = [
        Structure::EXO_STAFF_NAME => Structure::EXO_STAFF_NICKNAME,
        Structure::EXO_STAFF_JOBTITLE => Structure::EXO_STAFF_JOBTITLE,
        Structure::EXO_STAFF_EMAIL => Structure::EXO_STAFF_EMAIL,
        Structure::EXO_STAFF_PHONE_NUMBER => Structure::EXO_STAFF_PHONE_NUMBER
    ];

    /**
     * Search constructor.
     * @param Context $context
     * @param Result $result
     * @param RawFactory $resultRaw
     * @param JsonFactory $resultJson
     * @param Structure $structureHelper
     */
    public function __construct(
        Context $context,
        Result $result,
        RawFactory $resultRaw,
        JsonFactory $resultJson,
        Structure $structureHelper
    )
    {
        parent::__construct($context);
        $this->resultRaw = $resultRaw->create();
        $this->resultJson = $resultJson->create();
        $this->searchResult = $result;
        $this->structureHelper = $structureHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $query = $this->getRequest()->getParam('query');
        $result = $this->searchResult->getMatchedSalesRep($query);

        if (count($result) <= 0) {
            $result['is_empty'] = 1;
        }

        $this->resultJson->setData($result);

        return $this->resultJson;
    }
}
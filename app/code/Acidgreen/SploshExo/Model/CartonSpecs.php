<?php

namespace Acidgreen\SploshExo\Model;

use Acidgreen\SploshExo\Helper\Api as ApiHelper;
use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use Psr\Log\LoggerInterface;

use Acidgreen\SploshBox\Api\BoxRepositoryInterface;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Framework\Registry as CoreRegistry;
use Magento\Framework\Registry;
use Acidgreen\Exo\Helper\Data as ExoDataHelper;

class CartonSpecs
{
	const CONFIG_B2B_WEBSITE_ID_PATH = 'sploshbox_settings/b2b_settings/b2b_website_ids';
	/**
	 * @var ApiHelper
	 */
	protected $apiHelper;
	
	/**
	 * @var ConfigHelper
	 */
	protected $configHelper;
	
	/**
	 * 
	 * @var LoggerInterface
	 */
	protected $logger;
	
	/**
	 * @var BoxRepositoryInterface
	 */
	protected $boxRepository;
	
    /**
     * @var array
     */
	protected $cartonSpecsData;
	
	/**
	 * @var WebsiteCollectionFactory
	 */
	protected $websiteCollectionFactory;
	
	/**
	 * 
	 * @var CoreRegistry
	 */
	private $_coreRegistry;
	
	/**
	 * @var ExoDataHelper
	 */
	protected $exoDataHelper;
	
	/**
	 * Class construct
	 * @param ApiHelper $apiHelper
	 * @param ConfigHelper $configHelper
	 * @param LoggerInterface $logger
	 * @param BoxRepositoryInterface $boxRepository
	 * @param WebsiteCollectionFactory $websiteCollectionFactory
	 * @param CoreRegistry $coreRegistry
	 * @param ExoDataHelper $exoDataHelper
	 */
	public function __construct(
		ApiHelper $apiHelper,
		ConfigHelper $configHelper,
		LoggerInterface $logger,
		BoxRepositoryInterface $boxRepository,
		WebsiteCollectionFactory $websiteCollectionFactory,
		CoreRegistry $coreRegistry,
		ExoDataHelper $exoDataHelper
	) {
		$this->apiHelper = $apiHelper;
		$this->configHelper = $configHelper;
		$this->logger = $logger;
		
		$this->boxRepository = $boxRepository;
		$this->websiteCollectionFactory = $websiteCollectionFactory;
		
		$this->_coreRegistry = $coreRegistry;
		
		$this->exoDataHelper = $exoDataHelper;
	}
	
	public function startSync()
	{
		// $websites = $this->getWebsites();
		
		// foreach ($websites as $website) {
			
			// $this->configHelper->setExoCurrentWebsite($website->getId());
			
			// $this->logger->debug(__METHOD__. ' :: getExoCurrentWebsiteId :: '.$this->configHelper->getExoCurrentWebsiteId());
			$this->initCartonSpecsData();
			$this->initCartonSizes();
			
			// $this->configHelper->unsetExoCurrentWebsite();
		// }
	}
	
	public function initCartonSpecsData()
	{
        $page = 1;
        $params = ['page' => $page, 'pagesize' => 100];
        $tempCartonCount = 1;

        $cartonsXmlString = '<ArrayOfCustomTableObject>';
        while ($tempCartonCount > 0) {
            $tempCartonCount = 0;
            $params['page'] = $page;

            $response = $this->apiHelper->getAllCartonSpecs($params);
            
            $body = '';
            if ($response['status'] == '200')
            {
                $body = \GuzzleHttp\Ring\Core::body($response);
            }
            $pageBodyXml = new \SimpleXMLElement($body);
            $tempCartonCount = count($pageBodyXml);

            $body = str_replace('<ArrayOfCustomTableObject>', '', $body);
            $body = str_replace('</ArrayOfCustomTableObject>', '', $body);
            $body = str_replace('<ArrayOfCustomTableObject />', '', $body);
            $cartonsXmlString .= $body;

            $page++;
        }

        $cartonsXmlString .= '</ArrayOfCustomTableObject>';

        $cartonsXml = new \SimpleXMLElement($cartonsXmlString);
		
		$this->cartonSpecsData = $cartonsXml;
	}
	
	public function initCartonSizes()
	{
		$cartonSpecsData = $this->cartonSpecsData;
		$ctr = 1;
		foreach ($cartonSpecsData as $cartonData) {
			
			$boxType = $cartonData->xpath('ExtraFields[Key="CTN_SIZE"]/Value');
			$boxType = $boxType[0]->__toString();
			
			// we should find boxes by it's name
			$box = $this->boxRepository->getBoxByType([
				'box_type' => $boxType,
			]);
			if (!$box) {
				$this->logger->debug('init new instance of Box model...');
				$box = $this->boxRepository->create();
            } else {
                $this->logger->debug(__METHOD__.' :: BOX EXISTS UNDER ID #'.$box->getBoxId());
            }

			// set stuff here...
				
            $multiQty = $cartonData->xpath('ExtraFields[Key="MULTI_QTY"]/Value');
            $multiQty = $multiQty[0]->__toString();
            
            $isActive = $cartonData->xpath('ExtraFields[Key="ISACTIVE"]/Value');
            $isActive = $isActive[0]->__toString();
            $isActive = $this->exoDataHelper->getBooleanValue($isActive);

            $isMixedBox = $cartonData->xpath('ExtraFields[Key="MIXED_BOX"]/Value');
            $isMixedBox = $isMixedBox[0]->__toString();
            // debugging purposes only

            $isMixedBox = $this->exoDataHelper->getBooleanValue($isMixedBox);

            $this->logger->debug(__('%1 :: multiQty = %2, isActive = %3, isMixedBox = %4', __METHOD__, $multiQty, $isActive, $isMixedBox));
        
            $box->setBoxType($boxType);
            $box->setMultiQty($multiQty);
            $box->setIsActive($isActive);
            $box->setIsMixedBox($isMixedBox);
            
            $this->boxRepository->save($box);
            
			$this->logger->debug(__METHOD__.' :: carton data row :: '.$ctr);
            if (!empty($box->getBoxId()))
                $this->logger->debug(__METHOD__.' :: Saved under box ID #'. $box->getBoxId());

            // $this->logger->debug('box dump :: '.print_r($box->getData(), true));
            $ctr++;
			
		}
	}
	
	private function getWebsites()
	{
		$collection = $this->websiteCollectionFactory->create();
		
		$b2bWebsiteIds = $this->configHelper->getScopeConfigWebsite(self::CONFIG_B2B_WEBSITE_ID_PATH);
		$b2bWebsiteIds = explode(',', $b2bWebsiteIds);
		
		$collection->addFieldToFilter('website_id', ['in' => $b2bWebsiteIds]);
		
		return $collection;
	}
}

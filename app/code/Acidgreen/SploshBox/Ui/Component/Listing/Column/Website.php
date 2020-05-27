<?php

namespace Acidgreen\SploshBox\Ui\Component\Listing\Column;

use Psr\Log\LoggerInterface;
use Acidgreen\SploshBox\Model\B2BWebsite;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Website extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * 
     * @var B2BWebsite
     */
    protected $b2bWebsites;

    /**
     * 
     * @var array
     */
    protected $b2bWebsiteArray;
    
    public function __construct(
		ContextInterface $context, 
		UiComponentFactory $uiComponentFactory,
		array $components = [],
		array $data = [],
        LoggerInterface $logger,
    	B2BWebsite $b2bWebsites
    ) {
        $this->logger = $logger;
        
        $this->b2bWebsites = $b2bWebsites;
        $this->b2bWebsiteArray = [];

		$this->prepareWebsitesArray();
        
		parent::__construct($context, $uiComponentFactory, $components, $data);
    }

	public function prepareDataSource(array $dataSource)
	{
		$this->logger->debug(__METHOD__);

		if (isset($dataSource['data']['items'])) {
		    $this->logger->debug(print_r($dataSource['data']['items'], true));

            $websites = $this->getData('options');
            $fieldName = $this->getData('name');
            $this->logger->debug('Current subject column: '.$fieldName);

            $items = &$dataSource['data']['items'];
            $this->logger->debug('# of items = '.count($items));
            $ctr = 0;

            foreach ($dataSource['data']['items'] as $row) {
                $websiteIdData = $row['website_id'];
                foreach ($websites->toOptionArray() as $id => $website) {
                    if ($website['value'] == $websiteIdData) {
                        $this->logger->debug(__('Website for idx %1 :: %2', $ctr, $website['label']));
                        $dataSource['data']['items'][$ctr][$fieldName] = $website['label'];
                        // $row['website'] = $website['label'];
                        break;
                    }
                }
                ++$ctr;
            }

		}
		return $dataSource;
	}
	
	public function prepareWebsitesArray()
	{
		$websites = $this->b2bWebsites->getB2BWebsites();
		
		foreach ($websites as $website) {
			$this->b2bWebsiteArray[$website->getId()] = $website->getName();
		}
		
	}
}

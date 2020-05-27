<?php

namespace Acidgreen\SploshExo\Model\Import\Plugin\Product;

use Acidgreen\Exo\Model\Import\Product as ProductImportModel;
use Magento\ImportExport\Model\Import as ImportModel;
use Acidgreen\SploshExo\Helper\Import\Product\AdditionalImages as AdditionalImagesHelper;
use Psr\Log\LoggerInterface;

class AdditionalImages
{
	/**
	 * @var AdditionalImagesHelper
	 */
	protected $helper;
	/**
	 * @var ImportModel
	 */
	protected $importModel;
	
	/**
	 * @var LoggerInterface
	 */
	protected $logger;
	
	public function __construct(
		AdditionalImagesHelper $helper,
		ImportModel $importModel,
		LoggerInterface $logger
	) {
		$this->helper = $helper;
		$this->importModel = $importModel;
		$this->logger = $logger;
	}
	
	public function afterInitImport(ProductImportModel $model, $result)
	{
		$this->logger->debug(__METHOD__.' :: Went here...');
		
		$this->helper->importAdditionalImages();
		
		return $result;
	}
	
}

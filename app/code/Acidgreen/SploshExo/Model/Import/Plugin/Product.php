<?php

namespace Acidgreen\SploshExo\Model\Import\Plugin;

use Acidgreen\Exo\Model\Import\Product as ExoProductImportModel;
use Acidgreen\Exo\Model\Process;


use Acidgreen\SploshExo\Helper\Import\Product\Images as ProductImagesHelper;
use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;

use Psr\Log\LoggerInterface;

class Product
{
	const CONFIG_IS_ENABLED = 'acidgreen_exo_apisettings/sync_settings/replace_images_enabled';
	
	/**
	 * @var ProductImagesHelper
	 */
	protected $productImagesHelper;
	
	/**
	 * @var ConfigHelper
	 */
	protected $configHelper;
	
    /**
     * @var LoggerInterface
     */
    protected $logger;
    

    public function __construct(
    	ProductImagesHelper $productImagesHelper,
    	ConfigHelper $configHelper,
        LoggerInterface $logger
    ) {
    	$this->productImagesHelper = $productImagesHelper;
    	$this->configHelper = $configHelper;
        $this->logger = $logger;
    }

    /**
     * Plugin method to execute before ExoProductImportModel::initImport($process)
     * This unsets images attached to product.
     * @todo Use a helper instead
     * @param ExoProductImportModel $importModel
     * @param Process $process
     * @return Process $process
     */
    public function beforeInitImport(
        ExoProductImportModel $importModel,
        $process
    ) {
        // exit if disabled in config
        // SPL-188 - cleanup the source system config
        $enabled = false; // $this->configHelper->getScopeConfigWebsite(self::CONFIG_IS_ENABLED);
        
        if (!$enabled) {
			$this->logger->debug(__('%1 :: CANNOT DELETE IMAGES - Config disabled.', __METHOD__));
        	return [$process];
        }
        
        $this->productImagesHelper->removeAllImagesBeforeSync();


        return [$process];
    }
}

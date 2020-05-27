<?php

namespace Acidgreen\SploshExo\Cron;

use Acidgreen\Exo\Model\ResourceModel\Process\CollectionFactory as ProcessCollectionFactory;
use Acidgreen\Exo\Model\Process;
use Acidgreen\Exo\Model\Import\Product\StockPrice as ProductStockPrice;
use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use Acidgreen\Exo\Helper\Import as ImportHelper;
use Psr\Log\LoggerInterface;

class ProductStockPriceUpdate extends \Acidgreen\Exo\Cron\ProductStockPriceUpdate
{
    public function execute()
    {
        if ($this->configHelper->isModuleEnabled()) {
            // Set job to pending..then run it...
            $processes = $this->getProcesses();

            foreach ($processes as $process) {
                $this->logger->debug(__('%1 :: Set process "%2" to \'pending\'', __METHOD__, $process->getProcessName()));
                $process->setStatus(Process::STATUS_PENDING)->save();
            }
        }
    }

    private function getProcesses()
    {
        $collection = $this->processCollection->create();
        $collection->addFieldToFilter('process_type', Process::PROCESS_TYPE_PRODUCT_STOCKPRICE);
        $collection->addFieldToFilter('is_active', 1);

        return $collection;
    }
}

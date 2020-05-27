<?php

namespace Acidgreen\SploshExo\Cron;

use Acidgreen\Exo\Model\ProcessFactory;
use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use Acidgreen\Exo\Helper\Import as ImportHelper;
use Psr\Log\LoggerInterface as Logger;
use Acidgreen\Exo\Cron\Pending as ExoPendingCron;

class Pending extends ExoPendingCron
{
	/**
     * Run and set processes to pending
     *
     * @return $this
     */
	public function execute()
    {

        if($this->configHelper->isModuleEnabled()) {
            $this->logger->debug('ATTENTION :: Acidgreen_SploshExo :: Start set to pending existing processes');

            $collection = $this->processFactory->create()->getCollection();
            $collection->addFieldToFilter('is_active', \Acidgreen\Exo\Model\Process::PROCESS_STATUS_ACTIVE);
            // SPL-132 - don't include product_stockprice in here...
            $collection->addFieldToFilter('process_type', ['in' => ['customer', 'product', 'order', 'staff']]);

            foreach($collection as $data) {
                $data->setStatus(\Acidgreen\Exo\Model\Process::STATUS_PENDING);
            }
            $collection->save();
            $this->logger->debug('Set to pending and flushed');
        }


        return $this;
    }
}

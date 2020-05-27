<?php

namespace Acidgreen\SploshExo\Cron;

use Acidgreen\Exo\Model\ProcessFactory;
use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use Acidgreen\Exo\Helper\Import as ImportHelper;
use Psr\Log\LoggerInterface as Logger;

class Timeout
{
  const STATUS_TIMEOUT = 'timeout';

  /**
  * @var \Acidgreen\Exo\Model\ProcessFactory
  */
  protected $processFactory;

  /**
  * @var \Psr\Log\LoggerInterface
  */
  protected $logger;

  /**
  * @var \Acidgreen\Exo\Helper\Api\Config
  */
  protected $configHelper;

  /**
  * @var \Acidgreen\Exo\Helper\Import
  */
  protected $importHelper;

  /**
  * Construct
  *
  * @param ProcessFactory $processFactory
  * @param ConfigHelper $configHelper
  * @param Logger @logger
  */
  public function __construct(
      ProcessFactory $processFactory,
      ConfigHelper $configHelper,
      ImportHelper $importHelper,
      Logger $logger
  )
  {
      $this->processFactory = $processFactory;
      $this->configHelper = $configHelper;
      $this->importHelper = $importHelper;
      $this->logger = $logger;
  }

	/**
  * Run and set processes to pending
  *
  * @return $this
  */
	public function execute()
    {

        if($this->configHelper->isModuleEnabled()) {
            $this->logger->debug('CHECKING TIMED OUT PROCESSES');

            /**
             * SPL-356 fix - don't filter Customer syncs as they tend to run looooonger
             */
            $collection = $this->processFactory->create()
                ->getCollection()
                ->addFieldToFilter('is_active', \Acidgreen\Exo\Model\Process::PROCESS_STATUS_ACTIVE)
                ->addFieldToFilter('status', \Acidgreen\Exo\Model\Process::STATUS_PROCESSING)
                ->addFieldToFilter('last_run_date', array(
                    'to' => date('Y-m-d H:i:s', strtotime('-2 hour'))
                ))
                ->addFieldToFilter('process_type', ['neq' => 'customer']); // SPL-356 fix

                //Need to add time filter, date time model of magento?

            foreach($collection as $data) {
                $data->setStatus(self::STATUS_TIMEOUT);
                $this->logger->debug($data->getProcessName().' has timed out');
            }

            if (sizeof($collection) > 0) {
                $collection->save();
            }
        }


        return $this;
    }
}

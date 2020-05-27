<?php

namespace Acidgreen\SploshExo\Cron;

use Acidgreen\Exo\Model\ProcessFactory;
use Acidgreen\Exo\Helper\Api\Config as ApiConfigHelper;
use Psr\Log\LoggerInterface as Logger;

class CustomerSync
{
    const PROCESS_STATUS_ACTIVE = 1;
    const STATUS_PENDING = 'pending';

    /**
     * @var \Acidgreen\Exo\Model\Process
     */
    protected $processFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ApiConfigHelper
     */
    protected $apiConfigHelper;

    /**
     * CustomerSync constructor.
     * @param ProcessFactory $processFactory
     * @param ApiConfigHelper $configHelper
     * @param Logger $logger
     */
    public function __construct(
        ProcessFactory $processFactory,
        ApiConfigHelper $configHelper,
        Logger $logger
    )
    {
        $this->processFactory   = $processFactory->create();
        $this->apiConfigHelper  = $configHelper;
        $this->logger           = $logger;
    }

    /**
     * @return $this
     */
    public function execute()
    {

        if ($this->apiConfigHelper->isModuleEnabled()) {

            try {
                $collection = $this->processFactory->getCollection();
                $collection->addFieldToFilter('is_active', self::PROCESS_STATUS_ACTIVE);
                $collection->addFieldToFilter('process_type', 'customer');

                foreach ($collection as $data) {
                    $data->setStatus(self::STATUS_PENDING);
                }

                $collection->save();

            } catch (\Exception $e) {
                $this->logger->error($e->__toString());
            }
        }

        return $this;
    }
}
<?php

namespace Acidgreen\SploshExo\Helper;

use Acidgreen\Exo\Model\ProcessFactory;
use Acidgreen\Exo\Model\Import\ProductFactory as ProductImportModel;
use Acidgreen\Exo\Model\Import\CustomerFactory as CustomerImportModel;
use Acidgreen\Exo\Model\Import\OrderFactory as OrderImportModel;
use Acidgreen\Exo\Model\Import\Product\StockPriceFactory as ProductStockPriceImportModel;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Registry as CoreRegistry;
use Acidgreen\Exo\Helper\ImportModelObjectInterface;

class Import extends \Acidgreen\Exo\Helper\Import
{
    /**
     * Run Process on Pending Status
     *
     */
    public function runProcessOnPendingStatus()
    {

        $this->logger->debug('runProcessOnPendingStatus');

        try {

            $processModel = $this->processFactory->create();
            $process = $processModel->getSinglePendingProcess();

            /**
             * SPL-276 fix - $process could be a new Process model w/o details so we have to filter the process_type
             */
            if (empty($process) || empty($process->getProcessType())) {
                $this->logger->debug(__('%1 :: NO PENDING PROCESS', __METHOD__));
            }
            else if (!$processModel->isLocked()) {
                $this->logger->debug(__('%1 :: SINGLE PROCESS RUN (exo_sync_in_progress)', __METHOD__));

                $this->_coreRegistry->register('exo_sync_in_progress', '1');

                $this->logger->debug('Process ' . $process->getProcessName() . ' in progress...');

                $process->addToProgress();

                $importModel = $this->getImportModel($process->getProcessType());

                $importModel->create()->initImport($process);

                // add 10 second delay for QPS issues
                $this->logger->debug(__METHOD__.' :: Add 10 second delay for QPS issues...');
                sleep(10);

                $this->logger->debug(__('%1 :: UNSET SOME REGISTRY VARIABLE HERE (exo_sync_in_progress)', __METHOD__));
                $this->logger->debug('UNSET PROCESS ' . $process->getProcessName());

                $this->_coreRegistry->unregister('exo_sync_in_progress');
            } else {
                $this->logger->debug(__('%1 :: PROCESS LOCKED', __METHOD__));
            }

        } catch (\Exception $e) {


        	$this->logger->debug(__('%1 :: EXCEPTION :: UNSET SOME REGISTRY VARIABLE HERE (exo_sync_in_progress)', __METHOD__));

        	$this->_coreRegistry->unregister('exo_sync_in_progress');

            $this->logger->debug('ERROR @ method runProcessOnPendingStatus!!!');
            $this->logger->debug('Error Import'.$e->getMessage());
        }
    }
}

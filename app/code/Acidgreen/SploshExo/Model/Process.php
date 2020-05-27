<?php

namespace Acidgreen\SploshExo\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Message\ManagerInterface;


class Process extends \Acidgreen\Exo\Model\Process
{

    /**
     * Checks if process is running
     *
     * @return bool
    */
    public function isLocked()
    {
        $collection = $this->getCollection()
                ->addFieldToSelect('process_id')
                ->addFieldToSelect('status')
                ->addFieldToFilter('is_active', array('eq' => '1'));

        if (!empty($collection)) {
            foreach($collection as $process) {
                if ($process->getStatus() == self::STATUS_PROCESSING) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
  	 * Get single process with pending status
  	 *
  	 * @return Process $process
  	 */
  	public function getSinglePendingProcess()
  	{
  		    $process = $this->getCollection()
                          ->addFieldToSelect('*')
                          ->addFieldToFilter('status', array('eq' => self::STATUS_PENDING))
                          ->addFieldToFilter('is_active', array('eq' => '1'))
                          ->getFirstItem();

          return $process;
  	}

}

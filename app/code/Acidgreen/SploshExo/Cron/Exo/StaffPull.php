<?php

namespace Acidgreen\SploshExo\Cron\Exo;

use Psr\Log\LoggerInterface;

class StaffPull
{
	/**
	 * @var LoggerInterface
	 */
	protected $logger;
	
	public function __construct(
		LoggerInterface $logger
	) {
		$this->logger = $logger;
	}
	
	public function execute()
	{
		$this->logger->info(__METHOD__.' :: Magento 2 wazzz here..');
		
		// sync staff from here..
		return $this;
	}
}

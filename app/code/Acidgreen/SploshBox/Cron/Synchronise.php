<?php

namespace Acidgreen\SploshBox\Cron;

use Psr\Log\LoggerInterface;
use Acidgreen\SploshExo\Model\CartonSpecs;

class Synchronise
{
	
	/**
	 * @var CartonSpecs
	 */
	protected $boxes;
	
	/**
	 * @var LoggerInterface
	 */
	protected $logger;
	
	public function __construct(
		LoggerInterface $logger,
		CartonSpecs $boxes
	) {
		$this->logger = $logger;
		
		$this->boxes = $boxes;
	}
	
	public function execute()
	{
		$this->boxes->startSync();
	}
}

<?php

namespace Acidgreen\SploshExo\Plugin\Model\Records\Tosync;

class OrderCreate
{

    /**
     * to set exo_current_website_id
     * @var \Acidgreen\SploshExo\Helper\Api\Config 
     */
    protected $configHelper;

    /**
     * @var \Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var \Zend\Log\Writer\Stream
     */
    protected $writer;

    public function __construct(
        \Acidgreen\SploshExo\Helper\Api\Config $configHelper
    ) {
        $this->configHelper = $configHelper;
        $this->logger = new \Zend\Log\Logger;
        $this->writer = new \Zend\Log\Writer\Stream(BP . '/var/log/SPL-313-OrderCreate.log');

        $this->logger->addWriter($this->writer);
    }

    public function aroundExecute(
        \Acidgreen\Exo\Model\Records\Tosync\OrderCreate $orderCreateModel,
        callable $proceed,
        ...$args
    ) {

        $this->configHelper->setExoCurrentWebsite($args[0]->getWebsite());
        $this->logger->debug('SPL-313 DEBUG :: getExoCurrentWebsiteId() BEFORE $proceed :: '.$this->configHelper->getExoCurrentWebsiteId());
        
        $proceed(...$args);
        $this->configHelper->unsetExoCurrentWebsite();
        $this->logger->debug('SPL-313 DEBUG :: getExoCurrentWebsiteId() AFTER $proceed :: '.$this->configHelper->getExoCurrentWebsiteId());

        return;
    }
}

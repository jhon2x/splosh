<?php

namespace Acidgreen\Theme\Controller\Salesrep;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var \Zend\Log\Writer\Stream
     */
    protected $writer;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $blockFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        array $data = []
    ) {
        $this->blockFactory = $blockFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);

        $this->logger = new \Zend\Log\Logger();
        $this->writer = new \Zend\Log\Writer\Stream(BP . '/var/log/SPL-B2B-Optimization.log');
        $this->logger->addWriter($this->writer);
    }

    public function execute()
    {
        // create block, store it to JSON

    	/** @var \Acidgreen\Theme\Block\Html\Salesrep $salesrepBlock */
        $salesrepBlock = $this->blockFactory->createBlock('Acidgreen\Theme\Block\Html\Salesrep');
        $salesrepBlock->setTemplate('Magento_Theme::html/salesrep.phtml');
        
        $result = $this->resultJsonFactory->create();

        return $result->setData(['block' => $salesrepBlock->toHtml()]);
    }
}

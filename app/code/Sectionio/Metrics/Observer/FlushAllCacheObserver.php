<?php
namespace Sectionio\Metrics\Observer;

use Magento\Framework\Event\ObserverInterface;

class FlushAllCacheObserver implements ObserverInterface
{
    /**
     * Application config object
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    /**
     * @var \Sectionio\Metrics\Model\PurgeCache
     */
    private $purgeCache;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Sectionio\Metrics\Model\PurgeCache $purgeCache
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Sectionio\Metrics\Model\PurgeCache $purgeCache
    ) {
        $this->config = $config;
        $this->purgeCache = $purgeCache;
    }

    /**
     * Flash Varnish cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // @codingStandardsIgnoreLine (parameter is unused but required for interface)
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->config->isEnabled()) {
            $this->purgeCache->sendPurgeRequest('.*');
        }
    }
}

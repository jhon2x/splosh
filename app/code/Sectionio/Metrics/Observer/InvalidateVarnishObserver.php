<?php
namespace Sectionio\Metrics\Observer;

use Magento\Framework\Event\ObserverInterface;

class InvalidateVarnishObserver implements ObserverInterface
{
    /**
    * Split the tags to invalidate into batches of this size to avoid the API call URL being too long
    **/
    const TAGS_BATCH_SIZE = 50;

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
     * If Varnish caching is enabled it collects array of tags
     * of incoming object and asks to clean cache.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->config->isEnabled()) {
            $object = $observer->getEvent()->getObject();
            if ($object instanceof \Magento\Framework\DataObject\IdentityInterface) {
                $tags = [];
                $pattern = "((^|,)%s(,|$))";
                foreach ($object->getIdentities() as $tag) {
                    $tags[] = sprintf($pattern, $tag);
                }
                if (!empty($tags)) {
                    $batched_tags = array_chunk(array_unique($tags), self::TAGS_BATCH_SIZE);
                    foreach ($batched_tags as $batch) {
                        $this->purgeCache->sendPurgeRequest(implode('|', $batch));
                    }
                }
            }
        }
    }
}

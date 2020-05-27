<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-misspell
 * @version   1.0.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Misspell\Plugin;

use Mirasvit\Misspell\Model\Config;
use Mirasvit\Misspell\Model\Indexer;

class IndexerConfigDataPlugin
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param object $subject
     * @param \Closure $proceed
     * @param string $path
     * @param string $default
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     */
    public function aroundGet(
        $subject,
        \Closure $proceed,
        $path = null,
        $default = null
    ) {
        $data = $proceed($path, $default);

        if (!$this->config->isMisspellEnabled() && !$this->config->isFallbackEnabled()) {
            if (!$path && isset($data[Indexer::INDEXER_ID])) {
                unset($data[Indexer::INDEXER_ID]);
            } elseif ($path) {
                list($firstKey,) = explode('/', $path);
                if ($firstKey == Indexer::INDEXER_ID) {
                    $data = $default;
                }
            }
        }

        return $data;
    }
}

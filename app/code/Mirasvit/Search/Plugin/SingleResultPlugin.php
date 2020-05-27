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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Plugin;

use Mirasvit\Search\Block\Result;
use Mirasvit\Search\Model\Config;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\App\ResponseInterface;

class SingleResultPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LayerResolver
     */
    private $layerResolver;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $response;

    public function __construct(
        Config $config,
        LayerResolver $layerResolver,
        ResponseInterface $response
    ) {
        $this->config = $config;
        $this->layerResolver = $layerResolver;
        $this->response = $response;
    }

    /**
     * @param Result $block
     * @param string $html
     * @return string
     * @SuppressWarnings(PHPMD)
     */
    public function afterToHtml(
        Result $block,
        $html
    ) {
        if (!$this->config->isRedirectOnSingleResult()) {
            return $html;
        }

        if ($this->layerResolver->get()->getProductCollection()->getSize() == 1) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->layerResolver->get()->getProductCollection()->getFirstItem();

            $this->response
                ->setRedirect($product->getProductUrl())
                ->setStatusCode(301)
                ->sendResponse();
        }

        return $html;
    }
}

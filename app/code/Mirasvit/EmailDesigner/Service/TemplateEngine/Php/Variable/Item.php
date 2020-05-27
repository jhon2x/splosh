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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable;


use Magento\Framework\View\Element\BlockFactory;

class Item
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    public function __construct(BlockFactory $blockFactory)
    {
        $this->blockFactory = $blockFactory;
    }

    /**
     * @inheritdoc
     *
     * @param \Magento\Sales\Model\Order\Item $item
     */
    public function getItemOptions($item)
    {
        $result = [];
        if ($options = $item->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item|\Magento\Quote\Model\Quote\Item $item
     *
     * @return string
     */
    public function getPriceHtml($item)
    {
        /** @var \Magento\Weee\Block\Item\Price\Renderer $priceRender */
        $priceRender = $this->blockFactory->createBlock(\Magento\Weee\Block\Item\Price\Renderer::class);
        $result = $priceRender->setArea('frontend')
            ->setTemplate('item/price/unit.phtml')
            ->setZone('cart')
            ->setItem($item)
            ->toHtml();

        return $result;
    }
}

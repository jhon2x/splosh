<?php

namespace Acidgreen\SploshBackorder\Block\Plugin\Order\Item\Renderer;

class DefaultRenderer
{

    public function beforeSetTemplate(\Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $block, $template)
    {
        if ($template == 'order/items/renderer/default.phtml') 
            $template = 'Acidgreen_SploshBackorder::order/items/renderer/default.phtml';
        return [$template];
    }
}

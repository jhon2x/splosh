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



namespace Mirasvit\Search\Ui\ScoreRule\Form\Block;

use Magento\Framework\Data\Form\Element\AbstractElement;

class PostConditionsRenderer implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        /** @var \Mirasvit\Search\Model\ScoreRule\Rule $rule */
        $rule = $element->getRule();
        if ($rule && $rule->getPostConditions()) {
            return $rule->getPostConditions()->asHtmlRecursive();
        }
        return '';
    }
}

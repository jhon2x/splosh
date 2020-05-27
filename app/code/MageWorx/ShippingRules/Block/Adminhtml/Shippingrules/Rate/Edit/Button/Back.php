<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Block\Adminhtml\Shippingrules\Rate\Edit\Button;

/**
 * Class Back
 */
class Back extends Generic
{
    /**
     * Get back button data
     * Can redirect to the corresponding method form
     *
     * @param int $sortOrder
     * @return array
     */
    public function getButtonData($sortOrder = 10)
    {
        $url     = $this->resolveRedirectBackUrl();
        $label   = __('Back');
        $onClick = sprintf("location.href = '%s';", $url);
        $result  = [
            'label'      => $label,
            'on_click'   => $onClick,
            'class'      => 'back',
            'sort_order' => $sortOrder
        ];

        return $result;
    }

    /**
     * Resolve the redirect back url
     *
     * @return string
     */
    private function resolveRedirectBackUrl()
    {
        if ($this->isBackToMethod() && ($this->getRate()->getMethodCode() || $this->request->getParam('method_code'))) {
            // Return to the corresponding method's edit form
            $methodCode = $this->getRate()->getMethodCode() ?
                $this->getRate()->getMethodCode() :
                $this->request->getParam('method_code');

            $url = $this->getUrl(
                'mageworx_shippingrules/shippingrules_method/edit',
                [
                    'code' => $methodCode
                ]
            );
        } else {
            // Return to the grid
            $url = $this->getUrl('*/*/');
        }

        return $url;
    }
}

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



namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Filter;


class Url
{
    /**
     * This variable is set automatically in \Liquid\Filterbank on line #95
     * @var \Liquid\Context
     */
    public $context;

    /**
     * Resume customer's session and redirect to URL passed with $targetUrl.
     * @param string $targetUrl
     * @return string
     */
    public function resume($targetUrl)
    {
        /** @var \Mirasvit\Email\Model\Queue $queue */
        /** @var \Magento\Store\Model\Store $store */
        $queue = $this->context->get('queue');
        $store = $this->context->get('store');

        if ($queue && $store) {
            $params = ['hash' => $queue->getUniqHash(), 'to' => base64_encode($targetUrl)];

            $targetUrl = $store->getBaseUrl() . 'email/action/resume' . '?' . http_build_query($params);
        }

        return $targetUrl;
    }
}

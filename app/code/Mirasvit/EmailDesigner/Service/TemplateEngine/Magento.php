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



namespace Mirasvit\EmailDesigner\Service\TemplateEngine;

use Magento\Email\Model\Template as EmailTemplate;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\EmailDesigner\Api\Service\TemplateEngineInterface;
use Mirasvit\EmailDesigner\Model\Email\TemplateFactory as EmailTemplateFactory;

class Magento implements TemplateEngineInterface
{
    /**
     * @var EmailTemplateFactory
     */
    private $emailTemplateFactory;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        EmailTemplateFactory $templateFactory,
        Emulation $appEmulation,
        StoreManagerInterface $storeManager

    ) {
        $this->emailTemplateFactory = $templateFactory;
        $this->appEmulation         = $appEmulation;
        $this->storeManager         = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, array $variables = [])
    {
        /** @var EmailTemplate $emailTemplate */
        $emailTemplate = $this->emailTemplateFactory->create()->load($variables['template_id']);

        // stop environment emulation, it has been already applied, see \Mirasvit\Email\Model\Queue\Sender::send
        // to avoid conflict with nested environment emulation
        $this->appEmulation->stopEnvironmentEmulation();

        if (isset($variables['store_id'])) {
            $emailTemplate->emulateDesign($variables['store_id']);
        } else {
            $emailTemplate->emulateDesign($this->storeManager->getStore()->getId());
        }

        $emailTemplate->setTemplateText($template)
            ->setData('is_plain', false);

        $result = $emailTemplate->getProcessedTemplate($variables);

        $result = str_replace('&amp;', '&', $result); //bug in magento template processor

        //stop environment emulation afer rendering
        $this->appEmulation->stopEnvironmentEmulation();

        return $result;
    }
}

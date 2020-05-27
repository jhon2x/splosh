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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Ui\Trigger\Form\Control;

use Magento\Framework\Url;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Service\SessionInitiatorInterface;

class SendTestButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var \Mirasvit\Email\Model\Config
     */
    private $config;

    /**
     * @var Url
     */
    private $urlBuilder;

    /**
     * @var SessionInitiatorInterface
     */
    private $sessionInitiator;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    public function __construct(
        \Mirasvit\Email\Model\Config $config,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        Url $urlBuilder,
        SessionInitiatorInterface $sessionInitiator
    ) {
        $this->formKey = $context->getFormKey();
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        $this->sessionInitiator = $sessionInitiator;

        parent::__construct($context, $registry);
    }

    /**
     * {@inheritDoc}
     */
    public function getButtonData()
    {
        $data = [];
        $triggerId = $this->getTriggerId();

        if ($triggerId) {
            $data = [
                'label' => __('Send Test Email'),
                'sort_order' => 25,
                'on_click' => 'trigger.sendTestEmail(
                    this,
                    false,
                    \'' . $this->config->getTestEmail() . '\',
                    \'' . $this->getSaveUrl() . '\'
                )',
                'data_attribute' => [
                    'mage-init' => [
                        'trigger' => []
                    ],
                ],
            ];
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        $this->sessionInitiator->set($this->formKey->getFormKey()); //init front session to make possible to send emails

        return $this->urlBuilder->getUrl('email/action/send/', [
            TriggerInterface::ID => $this->getTriggerId()
        ]);
    }
}

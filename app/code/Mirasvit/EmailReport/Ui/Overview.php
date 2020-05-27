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
 * @package   mirasvit/module-email-report
 * @version   2.0.8
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailReport\Ui;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\EmailReport\Api\Data\ClickInterface;
use Mirasvit\EmailReport\Api\Data\EmailInterface;
use Mirasvit\EmailReport\Api\Data\OpenInterface;
use Mirasvit\EmailReport\Api\Data\OrderInterface;
use Mirasvit\EmailReport\Api\Data\ReviewInterface;
use Mirasvit\EmailReport\Api\Repository\ClickRepositoryInterface;
use Mirasvit\EmailReport\Api\Repository\EmailRepositoryInterface;
use Mirasvit\EmailReport\Api\Repository\OpenRepositoryInterface;
use Mirasvit\EmailReport\Api\Repository\OrderRepositoryInterface;
use Mirasvit\EmailReport\Api\Repository\ReviewRepositoryInterface;
use Mirasvit\EmailReport\Ui\Modifier\DataProviderModifier;

class Overview extends AbstractComponent
{
    private $dataProviderModifier;

    public function __construct(
        DataProviderModifier $dataProviderModifier,
        ContextInterface $context,
        $components = [],
        array $data = []
    ) {
        $this->dataProviderModifier = $dataProviderModifier;

        parent::__construct($context, $components, $data);
    }

    public function getComponentName()
    {
        return 'email_report_overview';
    }

    public function prepare()
    {
        $config = $this->getData('config');

        $data = [];

        if ($this->context->getRequestParam(CampaignInterface::ID)) {
            $data['id_field_name'] = CampaignInterface::ID;
            $data[CampaignInterface::ID] = $this->context->getRequestParam(CampaignInterface::ID);
        }

        $data = $this->dataProviderModifier->modifyData($data);

        $config = array_merge_recursive($config, $data);

        $this->setData('config', $config);

        parent::prepare();
    }
}

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



namespace Mirasvit\Email\Block\Adminhtml;

use Magento\Framework\DataObject;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Mirasvit\Core\Block\Adminhtml\AbstractMenu;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Repository\CampaignRepositoryInterface;

//use Mirasvit\EmailReport\Controller\Adminhtml\Report\Index as ReportIndexController;

class Menu extends AbstractMenu
{
    /**
     * @var CampaignRepositoryInterface
     */
    protected $campaignRepository;

    /**
     * @param CampaignRepositoryInterface $campaignRepository
     * @param Context                     $context
     */
    public function __construct(
        CampaignRepositoryInterface $campaignRepository,
        Context $context
    ) {
        $this->campaignRepository = $campaignRepository;

        $this->visibleAt(['email', 'email_designer']);

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildMenu()
    {
        $this->addItem([
            'id'       => 'campaign',
            'resource' => 'Mirasvit_Email::campaign',
            'title'    => __('Manage Campaigns'),
            'url'      => $this->urlBuilder->getUrl('email/campaign'),
        ])->addItem([
            'resource' => 'Mirasvit_Email::queue',
            'title'    => __('Mail Log'),
            'url'      => $this->urlBuilder->getUrl('email/queue'),
        ])->addItem([
            'resource' => 'Mirasvit_Email::event',
            'title'    => __('Event Log'),
            'url'      => $this->urlBuilder->getUrl('email/event'),
        ])->addItem([
            'resource' => 'Mirasvit_EmailDesigner::email_designer_template',
            'title'    => __('Manage Templates'),
            'url'      => $this->urlBuilder->getUrl('email_designer/template'),
        ])/*->addItem([
            'id'       => 'emailreport',
            'resource' => ReportIndexController::ADMIN_RESOURCE,
            'title'    => __('Statistics'),
            'url'      => $this->urlBuilder->getUrl('emailreport/report'),
        ])*/;

        $this->addSeparator();

        $this->addItem([
            'resource' => 'Mirasvit_Email::email_settings',
            'title'    => __('Settings'),
            'url'      => $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/email'),
        ]);

        foreach ($this->campaignRepository->getCollection() as $campaign) {
            $this->addItem([
                'resource' => 'Mirasvit_Email::campaign',
                'title'    => $campaign->getTitle(),
                'url'      => $this->urlBuilder->getUrl('email/campaign/view', [
                    CampaignInterface::ID => $campaign->getId()
                ]),
            ], 'campaign');
        }

        return $this;
    }
}

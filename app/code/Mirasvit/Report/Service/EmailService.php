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
 * @package   mirasvit/module-report
 * @version   1.3.77
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Service;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mirasvit\Report\Api\Data\EmailInterface;
use Mirasvit\Report\Api\Repository\Email\BlockRepositoryInterface;
use Mirasvit\Report\Api\Repository\EmailRepositoryInterface;
use Mirasvit\Report\Api\Service\EmailServiceInterface;

class EmailService implements EmailServiceInterface
{
    private $transportBuilder;

    private $emailRepository;

    private $scopeConfig;

    private $timezone;

    public function __construct(
        TransportBuilder $transportBuilder,
        EmailRepositoryInterface $emailRepository,
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timezone
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->emailRepository  = $emailRepository;
        $this->scopeConfig      = $scopeConfig;
        $this->timezone         = $timezone;
    }

    /**
     * {@inheritdoc}
     */
    public function send(EmailInterface $email)
    {
        $vars = [
            'subject' => $email->getSubject() . ' [' . $this->timezone->date()->format("M d, Y H:i") . ']',
            'blocks'  => "",
        ];

        $definedReports = $this->emailRepository->getReports();

        $blocks = $email->getBlocks();
        if (null === $blocks) {
            $blocks = [];
        }

        foreach ($blocks as $data) {
            if (isset($data['identifier'])) {
                $identifier = $data['identifier'];

                foreach ($definedReports as $report) {
                    if ($report['value'] == $identifier) {
                        /** @var BlockRepositoryInterface $repo */
                        $repo = $report['repository'];

                        if (!isset($data['timeRange'])) {
                            $data['timeRange'] = 'today';
                        }

                        $content = $repo->getContent($identifier, $data);

                        if ($content) {
                            $vars['blocks'] .= '<div class="block-wrapper">' . $content . '</div>';
                        }
                    }
                }
            }
        }

        $emails = explode(',', $email->getRecipient());

        foreach ($emails as $mail) {
            if (!trim($mail)) {
                continue;
            }

            /** @var \Magento\Framework\Mail\Transport $transport */
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('report_email')
                ->setTemplateOptions([
                    'area'  => FrontNameResolver::AREA_CODE,
                    'store' => 0,
                ])
                ->setTemplateVars($vars)
                ->setFrom([
                    'name'  => $this->scopeConfig->getValue('trans_email/ident_general/name'),
                    'email' => $this->scopeConfig->getValue('trans_email/ident_general/email'),
                ])
                ->addTo($mail)
                ->getTransport();

            $transport->sendMessage();
        }
    }
}
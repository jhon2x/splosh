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



namespace Mirasvit\Search\Service;

use Magento\Cms\Model\Template\FilterProvider as CmsFilterProvider;
use Magento\Email\Model\TemplateFactory as EmailTemplateFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\App\Emulation as AppEmulation;

class ContentService
{
    private $emulation;

    private $filterProvider;

    private $templateFactory;

    private $appState;

    private $moduleManager;

    public function __construct(
        AppEmulation $emulation,
        CmsFilterProvider $filterProvider,
        EmailTemplateFactory $templateFactory,
        AppState $appState,
        ModuleManager $moduleManager
    ) {
        $this->emulation       = $emulation;
        $this->filterProvider  = $filterProvider;
        $this->templateFactory = $templateFactory;
        $this->appState        = $appState;
        $this->moduleManager   = $moduleManager;
    }

    public function processHtmlContent($storeId, $html)
    {
        $this->emulation->startEnvironmentEmulation($storeId);

        $template = $this->templateFactory->create();
        $template->emulateDesign($storeId);

        $template->setTemplateText($html)
            ->setIsPlain(false);
        $template->setTemplateFilter($this->filterProvider->getPageFilter());
        $html = $template->getProcessedTemplate([]);

        if ($this->moduleManager->isEnabled('Gene_BlueFoot')) {
            $html = $this->appState->emulateAreaCode(
                'frontend',
                [$this, 'processBlueFoot'],
                [$html]
            );
        }

        $this->emulation->stopEnvironmentEmulation();

        return $html;
    }

    public function processBlueFoot($html)
    {
        $ob          = ObjectManager::getInstance();
        $stageRender = $ob->get('Gene\BlueFoot\Model\Stage\Render');
        $html        = $stageRender->render($html);

        return $html;
    }
}
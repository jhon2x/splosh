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
 * @package   mirasvit/module-misspell
 * @version   1.0.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Misspell\Observer;

use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Misspell\Helper\Query as QueryHelper;
use Mirasvit\Misspell\Model\Config;

/**
 * Class OnCatalogSearch
 */
class OnAjaxSuggestObserver extends OnCatalogSearchObserver implements ObserverInterface
{
    /**
     * @var QueryHelper
     */
    private $queryHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var HttpResponse
     */
    private $response;

    public function __construct(
        QueryHelper $queryHelper,
        Config $config,
        HttpResponse $response
    ) {
        $this->config = $config;
        $this->queryHelper = $queryHelper;
        $this->response = $response;

        parent::__construct($queryHelper, $config, $response);
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer)
    {
        if ($this->queryHelper->getNumResults() !== false
            && $this->queryHelper->getNumResults() !== null
            && $this->queryHelper->getNumResults() == 0
        ) {
            if ($this->config->isMisspellEnabled()) {
                $this->doSpellCorrection();
            }
        }
    }
}

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
 * @package   mirasvit/module-search-report
 * @version   1.0.5
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchReport\Block\Adminhtml;

use Magento\Framework\DataObject;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Mirasvit\Core\Block\Adminhtml\AbstractMenu;

class Menu extends AbstractMenu
{
    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->visibleAt(['searchReport']);

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildMenu()
    {
        $this->addItem([
            'resource' => 'Mirasvit_SearchReport::search_report',
            'title'    => __('Search Volume'),
            'url'      => $this->urlBuilder->getUrl('searchReport/report/view', ['report' => 'search_report_volume']),
        ])->addItem([
            'resource' => 'Mirasvit_SearchReport::search_report',
            'title'    => __('Search Terms'),
            'url'      => $this->urlBuilder->getUrl('searchReport/report/view', ['report' => 'search_report_query']),
        ]);

        return $this;
    }
}
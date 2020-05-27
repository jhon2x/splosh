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
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Model\Rule\Action;

use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\LayoutInterface;
use Magento\Rule\Model\ActionFactory;

class Collection extends \Magento\Rule\Model\Action\Collection
{

    public function __construct(
        AssetRepository $assetRepo,
        LayoutInterface $layout,
        ActionFactory $actionFactory,
        array $data = []
    ) {
        parent::__construct($assetRepo, $layout, $actionFactory, $data);
//        $this->setType(self::class);
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $actions = parent::getNewChildSelectOptions();
        $actions = array_merge_recursive(
            $actions,
            [
                ['value' => 'Mirasvit\Helpdesk\Model\Rule\Action\Ticket', 'label' => __('Update the Ticket')],
            ]
        );

        return $actions;
    }
}

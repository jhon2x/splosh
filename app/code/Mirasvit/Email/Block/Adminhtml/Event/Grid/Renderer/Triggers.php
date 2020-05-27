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



namespace Mirasvit\Email\Block\Adminhtml\Event\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Mirasvit\Email\Api\Service\EventManagementInterface;
use Mirasvit\Event\Api\Data\EventInterface;

class Triggers extends AbstractRenderer
{
    /**
     * @var EventManagementInterface
     */
    private $eventManagement;

    /**
     * Constructor
     *
     * @param EventManagementInterface $eventManagement
     * @param Context                  $context
     * @param array                    $data
     */
    public function __construct(
        EventManagementInterface $eventManagement,
        Context $context,
        array $data = []
    ) {
        $this->eventManagement = $eventManagement;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render(DataObject $row)
    {
        $triggerIds = $this->eventManagement->getProcessedTriggerIds($row->getData(EventInterface::ID));

        $arr = [];
        foreach ($triggerIds as $info) {
            $arr[$info['trigger_id']] = $info['status'];
        }

        return $this->arrayToTable($arr);
    }

    /**
     * Convert array to html table
     *
     * @param array $args
     * @return string
     */
    public function arrayToTable($args)
    {
        ksort($args);

        $html = '<table class="email__args-table">';
        foreach ($args as $key => $value) {
            $html .= '<tr>';
            $html .= '<td>' . $key . '</td>';
            $html .= '<td>' . $value . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        return $html;
    }
}

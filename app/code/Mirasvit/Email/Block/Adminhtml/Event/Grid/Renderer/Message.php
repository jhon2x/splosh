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

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Mirasvit\Event\Api\Data\EventInterface;
use Magento\Backend\Block\Context;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

class Message extends AbstractRenderer
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository, Context $context, array $data = [])
    {
        parent::__construct($context, $data);

        $this->eventRepository = $eventRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function render(DataObject $row)
    {
        $params = \Zend_Json::decode($row->getData(EventInterface::PARAMS_SERIALIZED));

        $identifier = $row[EventInterface::IDENTIFIER];

        $event = $this->eventRepository->getInstance($identifier);

        $string = $event->toString($params);

        return '<div style="white-space: normal; max-height: 20rem; overflow: scroll">' . $string . '</div>';
    }
}

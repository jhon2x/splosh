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



namespace Mirasvit\Event\Ui\Event\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirasvit\Event\Api\Data\EventInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

class Params extends Column
{
    private $eventRepository;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->eventRepository = $eventRepository;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $params = \Zend_Json::decode($item[EventInterface::PARAMS_SERIALIZED]);

                $identifier = $item[EventInterface::IDENTIFIER];

                $event = $this->eventRepository->getInstance($identifier);

                $string = $event->toString($params);

                $item[$this->getName()] = $this->wrap($string);
            }
        }

        return $dataSource;
    }

    private function wrap($html)
    {
        return '<div style="white-space: normal; max-height: 20rem; overflow: scroll">'
            . $html . '</div>';
    }
}

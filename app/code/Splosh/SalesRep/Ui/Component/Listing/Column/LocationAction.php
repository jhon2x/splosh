<?php

namespace Splosh\SalesRep\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class LocationAction extends Column
{
    const URL_ROW_EDIT = 'splosh_salesrep/location/edit';
    const URL_ROW_DELETE = 'splosh_salesrep/location/delete';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * RowAction constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['id'])) {
                    $rowId = $item['id'];
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_ROW_EDIT, [
                            'id' => $rowId
                        ]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_ROW_DELETE, [
                            'id' => $rowId
                        ]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete record $1', $rowId),
                            'message' => __('Are you sure ?'),
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
<?php

namespace Acidgreen\SploshExo\Ui\Components\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class StaffAction extends Column
{
    const URL_EDIT = 'acidgreen_sploshexo/staff/edit';

    /**
     * StaffAction constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
                        'href' => $this->context->getUrl(self::URL_EDIT, [
                            'id' => $rowId
                        ]),
                        'label' => __('Edit')
                    ];
                }
            }
        }

        return $dataSource;
    }
}
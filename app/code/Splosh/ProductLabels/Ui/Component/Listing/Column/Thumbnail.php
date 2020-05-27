<?php

namespace Splosh\ProductLabels\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Splosh\ProductLabels\Helper\Data as Helper;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Helper\Image;

/**
 * Class Thumbnail
 * @package Splosh\ProductLabels\Ui\Component\Listing\Column
 */
class Thumbnail extends Column
{
    const ALT_FIELD = 'name';

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Thumbnail constructor.
     * @param Image $imageHelper
     * @param UrlInterface $urlBuilder
     * @param Helper $helper
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Image $imageHelper,
        UrlInterface $urlBuilder,
        Helper $helper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper  = $imageHelper;
        $this->urlBuilder   = $urlBuilder;
        $this->helper       = $helper;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $url          = '';
                $thumbnailUrl = '';
                $link         = $this->urlBuilder->getUrl(
                    Helper::URL_PATH_EDIT,
                    ['id' => $item['label_id']]
                );
                if ($item[$fieldName] != '') {
                    $url          = $this->helper->getImageUrl($item[$fieldName]);
                    $thumbnailUrl = $this->helper->getImageUrl($item[$fieldName]);
                }
                $item[$fieldName . '_src']      = $thumbnailUrl;
                $item[$fieldName . '_alt']      = $this->getAlt($item) ?: '';
                $item[$fieldName . '_link']     = $link;
                $item[$fieldName . '_orig_src'] = $url;
            }
        }

        return $dataSource;
    }
}
<?php

namespace Splosh\ProductLabels\Model\Label;

use Splosh\ProductLabels\Model\ResourceModel\Label\CollectionFactory;
use Splosh\ProductLabels\Model\ResourceModel\Label\Collection;
use Splosh\ProductLabels\Model\Label;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Splosh\ProductLabels\Helper\Data as Helper;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $labelCollectionFactory
     * @param Helper $helper
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $labelCollectionFactory,
        Helper $helper,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $labelCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Label $label */
        foreach ($items as $label) {
            $this->loadedData[$label->getId()] = $label->getData();
            $image  = $label->getImage();
            if ($image) {
                $imagePathParts = explode('/', $image);
                $imageName = array_pop($imagePathParts);
                $imageData = [
                    'name' => $imageName,
                    'url'  => $this->helper->getImageUrl($image, Helper::IMAGE_TYPE_FORM_PREVIEW),
                    'path' => $image,
                    'size' => $this->helper->getImageOrigSize($image)
                ];
                $this->loadedData[$label->getId()]['image'] = [$imageData];
            }
        }

        $data = $this->dataPersistor->get('splosh_productlabels');
        if (!empty($data)) {
            $label = $this->collection->getNewEmptyItem();
            $label->setData($data);
            $this->loadedData[$label->getId()] = $label->getData();
            $this->dataPersistor->clear('splosh_productlabels');
        }

        return $this->loadedData;
    }
}

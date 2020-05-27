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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailDesigner\Ui\Template\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;
use Mirasvit\EmailDesigner\Api\Repository\TemplateRepositoryInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var PoolInterface
     */
    private $poolInterface;

    public function __construct(
        PoolInterface $poolInterface,
        TemplateRepositoryInterface $templateRepository,
        UiComponentFactory $uiComponentFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->poolInterface = $poolInterface;
        $this->templateRepository = $templateRepository;
        $this->collection = $this->templateRepository->getCollection();
        $this->uiComponentFactory = $uiComponentFactory;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        foreach ($this->poolInterface->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        /** @var $item TemplateInterface */
        foreach ($this->collection as $item) {
            $result = $item->getData();
            // print each area
            foreach ($item->getAreas() as $code => $label) {
                $result[$code] = $item->getAreaText($code);
            }

            $this->loadedData[$item->getId()] = $result;
        }

        return $this->loadedData;
    }
}

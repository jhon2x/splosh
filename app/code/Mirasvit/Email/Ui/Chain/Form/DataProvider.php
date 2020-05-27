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



namespace Mirasvit\Email\Ui\Chain\Form;

use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Repository\QueueRepositoryInterface;
use Mirasvit\Email\Api\Repository\Trigger\ChainRepositoryInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var ChainRepositoryInterface
     */
    private $chainRepository;

    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    public function __construct(
        ContextInterface $context,
        QueueRepositoryInterface $queueRepository,
        ChainRepositoryInterface $chainRepository,
        UiComponentFactory $uiComponentFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->context = $context;
        $this->queueRepository = $queueRepository;
        $this->chainRepository = $chainRepository;
        $this->uiComponentFactory = $uiComponentFactory;
        $this->collection = $this->chainRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        return parent::getMeta();
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        /** @var $item ChainInterface */
        foreach ($this->collection as $item) {
            $item = $this->chainRepository->get($item->getId());
            $data = $item->getData();
            $data['id_field_name'] = 'queue_id';
            $data['queue_id'] = ['in' => $this->getQueueIds($item->getId())];

            $data['id_field_name'] = $this->getRequestFieldName();
            $this->loadedData[$item->getId()] = $data;

            if ($this->context->getRequestParam($this->getRequestFieldName()) === $item->getId()
                && isset($data['report'])
            ) {
                $this->loadedData['report'] = $data['report'];
            }
        }

        return $this->loadedData;
    }

    /**
     * @param int $chainId
     *
     * @return int[]
     */
    private function getQueueIds($chainId)
    {
        return $this->queueRepository->getCollection()
            ->addFieldToFilter(ChainInterface::ID, $chainId)
            ->getColumnValues(QueueInterface::ID);
    }
}

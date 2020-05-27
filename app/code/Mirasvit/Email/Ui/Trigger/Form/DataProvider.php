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



namespace Mirasvit\Email\Ui\Trigger\Form;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\Trigger\ChainRepositoryInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class DataProvider extends AbstractDataProvider
{
    const TRIGGER = 'trigger';
    const CHAIN   = 'chain';

    /**
     * @var TriggerRepositoryInterface
     */
    private $triggerRepository;

    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var ChainRepositoryInterface
     */
    private $chainRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Registry $registry,
        RequestInterface $request,
        ChainRepositoryInterface $chainRepository,
        TriggerRepositoryInterface $triggerRepository,
        UiComponentFactory $uiComponentFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->chainRepository = $chainRepository;
        $this->triggerRepository = $triggerRepository;
        $this->collection = $triggerRepository->getCollection();
        $this->uiComponentFactory = $uiComponentFactory;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $triggerId = $this->request->getParam($this->getRequestFieldName());
        $trigger = $this->triggerRepository->get($triggerId);

        // Register flags to display rules
        $this->registry->register('event_formName', 'email_trigger_audience', true);
        if ($trigger) {
            $this->registry->register('event_eventIdentifier', $trigger->getEvent(), true);
            $this->registry->register('event_ruleConditions', $trigger->getRule(), true);
        }

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

        /** @var $item TriggerInterface */
        foreach ($this->collection as $item) {
            $item = $this->triggerRepository->get($item->getId());
            $data = $item->getData();
            $data[self::CHAIN] = [];
            $data['id_field_name'] = $this->getRequestFieldName();
            $data[TriggerInterface::CANCELLATION_EVENT] = $item->getCancellationEvent(); // convert to array
            unset($data[TriggerInterface::RULE]); // remove dynamic fields
            /** @var $chain ChainInterface */
            foreach ($item->getChainCollection() as $chain) {
                $chain = $this->chainRepository->get($chain->getId());
                $data[self::CHAIN][$chain->getId()] = $chain->getData();
                $data[self::CHAIN][$chain->getId()][ChainInterface::EXCLUDE_DAYS] = $chain->getExcludeDays();
            }

            // set chains for modifier
            $data[ChainInterface::ID] = ['in' => array_keys($data[self::CHAIN])];

            $this->loadedData[$item->getId()] = $data;
            if ($this->request->getParam($this->getRequestFieldName()) === $item->getId()
                && isset($data['report'])
            ) {
                $this->loadedData['report'] = $data['report'];
            }
        }

        return $this->loadedData;
    }
}

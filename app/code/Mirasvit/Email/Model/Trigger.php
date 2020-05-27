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



namespace Mirasvit\Email\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\Trigger\ChainRepositoryInterface;
use Mirasvit\Email\Model\Trigger\Handler;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;
use Mirasvit\Event\Api\Service\ValidatorServiceInterface;
use Mirasvit\Event\Service\EventService;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Trigger extends AbstractModel implements TriggerInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'email_trigger';

    /**
     * @var \Mirasvit\Email\Model\ResourceModel\Trigger\Chain\Collection
     */
    protected $chainCollection;

    /**
     * @var ChainRepositoryInterface
     */
    protected $chainRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var EventService
     */
    private $eventService;
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;
    /**
     * @var ValidatorServiceInterface
     */
    private $validatorService;

    public function __construct(
        ValidatorServiceInterface $validatorService,
        EventRepositoryInterface $eventRepository,
        EventService $eventService,
        Handler $handler,
        ChainRepositoryInterface $chainRepository,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Context $context,
        Registry $registry
    ) {
        $this->validatorService = $validatorService;
        $this->handler = $handler;
        $this->eventRepository = $eventRepository;
        $this->chainRepository = $chainRepository;
        $this->eventService = $eventService;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;

        $this->context = $context;
        $this->registry = $registry;

        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Mirasvit\Email\Model\ResourceModel\Trigger::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getChainCollection()
    {
        if ($this->chainCollection == null) {
            $this->chainCollection = $this->chainRepository->getCollection()
                ->addFieldToFilter(self::ID, $this->getId());
        }

        return $this->chainCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getTriggeringEvents()
    {
        return [$this->getData(self::EVENT)];
    }

    /**
     * {@inheritdoc}
     */
    public function getCancellationEvents()
    {
        return $this->getCancellationEvent();
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return array_values(array_unique(array_merge($this->getTriggeringEvents(), $this->getCancellationEvents())));
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleSerialized()
    {
        return $this->getData(self::RULE_SERIALIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleSerialized($value)
    {
        return $this->setData(self::RULE_SERIALIZED, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getRule()
    {
        if (!$this->hasData(self::RULE) && $this->getRuleSerialized()) {
            $data = \Zend_Json_Decoder::decode($this->getRuleSerialized());
            $this->setData(self::RULE, $data);
        } elseif (!$this->hasData(self::RULE) && !$this->getRuleSerialized()) {
            $this->setData(self::RULE, []);
        }

        return $this->getData(self::RULE);
    }

    /**
     * {@inheritDoc}
     */
    public function setRule($rule)
    {
        $this->setData(self::RULE, $rule);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSenderEmail($storeId = 0)
    {
        if ($this->getData(self::SENDER_EMAIL)) {
            return $this->getData(self::SENDER_EMAIL);
        }

        return $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSenderName($storeId = 0)
    {
        if ($this->getData(self::SENDER_NAME)) {
            return $this->getData(self::SENDER_NAME);
        }

        return $this->scopeConfig->getValue(
            'trans_email/ident_general/name',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Send test email
     *
     * @param null|string $to
     * @return true
     */
    public function sendTest($to = null)
    {
        $storeIds = $this->getStoreIds();
        if ($storeIds[0] == 0) {
            unset($storeIds[0]);
            foreach ($this->storeManager->getStores() as $storeId => $store) {
                if ($store->getIsActive()) {
                    $storeIds[] = $storeId;
                }
            }
        }

        foreach ($storeIds as $storeId) {
            $params = $this->eventService->getRandomParams($storeId);

            if ($to) {
                $params->setData('customer_email', $to);
            }

            $params['force'] = true;
            $params['is_test'] = true;

            $event = $this->eventRepository->create()
                ->setStoreId($storeId)
                ->setIdentifier($this->getEvent())
                ->setParams($params->getData())
                ->setKey('test_' . time())
                ->setUpdatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT))
                ->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

            $this->eventRepository->save($event);

            ini_set('display_errors', 1);

            $this->handler->triggerEvent($this, $event);
        }

        return true;
    }

    /**
     * Validate event args by trigger rules
     *
     * @param array $args
     * @param bool $force force validate
     *
     * @return bool
     */
    public function validateRules($args, $force = false)
    {
        if (isset($args['force']) && !$force) {
            return true;
        }

        $eventInstance = $this->eventRepository->getInstance($this->getEvent());

        $result = $this->validatorService->validate($this->getRule(), $eventInstance->expand($args));

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * {@inheritDoc}
     */
    public function setTitle($title)
    {
        $this->setData(self::TITLE, $title);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION, $description);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSchedule()
    {
        return $this->getData(self::SCHEDULE);
    }

    /**
     * {@inheritDoc}
     */
    public function setSchedule($schedule)
    {
        $this->setData(self::SCHEDULE, $schedule);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTriggerType()
    {
        return $this->getData(self::TRIGGER_TYPE);
    }

    /**
     * {@inheritDoc}
     */
    public function setTriggerType($triggerType)
    {
        $this->setData(self::TRIGGER_TYPE, $triggerType);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEvent()
    {
        return $this->getData(self::EVENT);
    }

    /**
     * {@inheritDoc}
     */
    public function setEvent($event)
    {
        $this->setData(self::EVENT, $event);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCancellationEvent()
    {
        $cancellationEvent = $this->getData(self::CANCELLATION_EVENT);
        if (!is_array($cancellationEvent)) {
            $cancellationEvent = array_filter(explode(',', $cancellationEvent));
        }

        return $cancellationEvent;
    }

    /**
     * {@inheritDoc}
     */
    public function setCancellationEvent($cancellationEvent)
    {
        if (is_array($cancellationEvent)) {
            $cancellationEvent = implode(',', $cancellationEvent);
        }

        $this->setData(self::CANCELLATION_EVENT, $cancellationEvent);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getGaSource()
    {
        return $this->getData(self::GA_SOURCE);
    }

    /**
     * {@inheritDoc}
     */
    public function setGaSource($source)
    {
        $this->setData(self::GA_SOURCE, $source);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getGaMedium()
    {
        return $this->getData(self::GA_MEDIUM);
    }

    /**
     * {@inheritDoc}
     */
    public function setGaMedium($medium)
    {
        $this->setData(self::GA_MEDIUM, $medium);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getGaName()
    {
        return $this->getData(self::GA_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setGaName($name)
    {
        $this->setData(self::GA_NAME, $name);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getGaTerm()
    {
        return $this->getData(self::GA_TERM);
    }

    /**
     * {@inheritDoc}
     */
    public function setGaTerm($term)
    {
        $this->setData(self::GA_TERM, $term);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getGaContent()
    {
        return $this->getData(self::GA_CONTENT);
    }

    /**
     * {@inheritDoc}
     */
    public function setGaContent($content)
    {
        $this->setData(self::GA_CONTENT, $content);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getStoreIds()
    {
        $storeIds = $this->getData(self::STORE_IDS);
        if (is_string($storeIds)) {
            $storeIds = explode(',', $storeIds);
        }

        return $storeIds;
    }

    /**
     * {@inheritDoc}
     */
    public function setStoreIds($storeIds)
    {
        if (is_array($storeIds)) {
            $storeIds = implode(',', $storeIds);
        }

        $this->setData(self::STORE_IDS, $storeIds);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCampaignId($campaignId)
    {
        $this->setData(CampaignInterface::ID, $campaignId);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCampaignId()
    {
        return $this->getData(CampaignInterface::ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritDoc}
     */
    public function setIsActive($isActive)
    {
        $this->setData(self::IS_ACTIVE, $isActive);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setSenderEmail($senderEmail)
    {
        $this->setData(self::SENDER_EMAIL, $senderEmail);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setSenderName($senderName)
    {
        $this->setData(self::SENDER_NAME, $senderName);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveTo()
    {
        return $this->getData(self::ACTIVE_TO);
    }

    /**
     * {@inheritDoc}
     */
    public function setActiveTo($activeTo)
    {
        $this->setData(self::ACTIVE_TO, $activeTo);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveFrom()
    {
        return $this->getData(self::ACTIVE_FROM);
    }

    /**
     * {@inheritDoc}
     */
    public function setActiveFrom($activeFrom)
    {
        $this->setData(self::ACTIVE_FROM, $activeFrom);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getIsAdmin()
    {
        return $this->getData(self::IS_ADMIN);
    }

    /**
     * {@inheritDoc}
     */
    public function setIsAdmin($value)
    {
        return $this->setData(self::IS_ADMIN, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getAdminEmail()
    {
        return $this->getData(self::ADMIN_EMAIL);
    }

    /**
     * {@inheritDoc}
     */
    public function setAdminEmail($email)
    {
        if (empty($email)) {
            $this->setIsAdmin(self::IS_ADMIN_DISABLED);
        } else {
            $this->setIsAdmin(self::IS_ADMIN_ACTIVE);
        }

        return $this->setData(self::ADMIN_EMAIL, $email);
    }
}

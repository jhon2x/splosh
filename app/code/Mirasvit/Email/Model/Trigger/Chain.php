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



namespace Mirasvit\Email\Model\Trigger;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Helper\Data;
use Mirasvit\Email\Model\Config\Source\CrossSell;
use Mirasvit\EmailDesigner\Api\Repository\TemplateRepositoryInterface;

/**
 * @method bool   getCouponEnabled()
 * @method int    getCouponSalesRuleId()
 * @method int    getCouponExpiresDays
 */
class Chain extends AbstractModel implements ChainInterface
{
    /**
     * @var TemplateRepositoryInterface
     */
    protected $templateRepository;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var CrossSell
     */
    private $crossSellSource;

    public function __construct(
        TemplateRepositoryInterface $templateRepository,
        CrossSell $crossSellSource,
        Data $helper,
        Context $context,
        Registry $registry
    ) {
        $this->helper = $helper;
        $this->templateRepository = $templateRepository;

        parent::__construct($context, $registry);
        $this->crossSellSource = $crossSellSource;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Mirasvit\Email\Model\ResourceModel\Trigger\Chain::class);
    }

    /**
     * Design template
     *
     * @return \Mirasvit\EmailDesigner\Model\Template
     */
    public function getTemplate()
    {
        return $this->templateRepository->get($this->getTemplateId());
    }

    /**
     * Get email chain hours.
     *
     * @param bool $inSeconds
     *
     * @return int
     */
    public function getDay($inSeconds = false)
    {
        $day = $this->getData(ChainInterface::DAY);
        if ($inSeconds) {
            $day *= 60 * 60 * 24;
        }

        return $day;
    }

    /**
     * Get email chain hours.
     *
     * @param bool $inSeconds
     *
     * @return int
     */
    public function getHour($inSeconds = false)
    {
        $hours = $this->getData(ChainInterface::HOUR);
        if ($inSeconds) {
            $hours *= 60 * 60;
        }

        return $hours;
    }

    /**
     * Get email chain minutes.
     *
     * @param bool $inSeconds
     *
     * @return int
     */
    public function getMinute($inSeconds = false)
    {
        $minutes = $this->getData(ChainInterface::MINUTE);
        if ($inSeconds) {
            $minutes *= 60;
        }

        return $minutes;
    }

    /**
     * @todo RF
     *
     * {@inheritdoc}
     */
    public function getScheduledAt($time)
    {
        $excludeDays = $this->getExcludeDays();
        $frequency = $this->getDay(true);
        $hours = $this->getHour(true);
        $minutes = $this->getMinute(true);

        $scheduledAt = $time + $frequency + $hours + $minutes;

        $scheduledAt = $scheduledAt + $this->addExcludedDays($scheduledAt, $excludeDays) * 86400;

        return $scheduledAt;
    }

    /**
     * Add excluded days.
     *
     * @param int $time
     * @param array $excludeDaysOfWeek
     * @return int
     */
    protected function addExcludedDays($time, $excludeDaysOfWeek)
    {
        $result = 0;
        if (is_array($excludeDaysOfWeek) && (count($excludeDaysOfWeek) > 0)) {
            while (in_array(date('w', $time + $result * 86400), $excludeDaysOfWeek)) {
                ++$result;

                if ($result > 7) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setTemplateId($id)
    {
        if (is_numeric($id)) {
            // add EmailDesigner prefix by default
            $id = \Mirasvit\EmailDesigner\Model\Template::TYPE . ':' . $id;
        }

        $this->setData(self::TEMPLATE_ID, $id);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplateId()
    {
        return $this->getData(self::TEMPLATE_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getTriggerId()
    {
        return $this->getData(TriggerInterface::ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setTriggerId($id)
    {
        $this->setData(TriggerInterface::ID, $id);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setDay($day)
    {
        $this->setData(self::DAY, $day);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setHour($hour)
    {
        $this->setData(self::HOUR, $hour);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMinute($minute)
    {
        $this->setData(self::MINUTE, $minute);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSendFrom()
    {
        return $this->getData(self::SEND_FROM);
    }

    /**
     * {@inheritDoc}
     */
    public function getSendTo()
    {
        return $this->getData(self::SEND_TO);
    }

    /**
     * {@inheritDoc}
     */
    public function setSendFrom($sendFrom)
    {
        $this->setData(self::SEND_FROM, $sendFrom);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setSendTo($sendTo)
    {
        $this->setData(self::SEND_TO, $sendTo);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getExcludeDays()
    {
        $excludeDays = $this->getData(self::EXCLUDE_DAYS);
        if (is_string($excludeDays)) {
            $excludeDays = explode(',', $excludeDays);
        }

        return $excludeDays;
    }

    /**
     * {@inheritDoc}
     */
    public function setExcludeDays($excludeDays)
    {
        if (is_array($excludeDays)) {
            $excludeDays = implode(',', $excludeDays);
        }

        $this->setData(self::EXCLUDE_DAYS, $excludeDays);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCrossSellsEnabled()
    {
        return $this->getData(self::CROSS_SELLS_ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function getCrossSellsTypeId()
    {
        return $this->getData(self::CROSS_SELLS_TYPE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getCrossSellMethodName()
    {
        $methodName = null;

        switch ($this->getCrossSellsTypeId()) {
            case CrossSell::MAGE_CROSS:
                $methodName = 'getCrossSellProductIds';
                break;
            case CrossSell::MAGE_RELATED:
                $methodName = 'getRelatedProductIds';
                break;
            case CrossSell::MAGE_UPSELLS:
                $methodName = 'getUpSellProductIds';
                break;
        }

        return $methodName;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function toString($format = '')
    {
        $delay = '';

        if ($this->getDay()) {
            $delay .= " <b>{$this->getDay()}</b>" . ($this->getDay() > 1 ? ' days' : 'day');
        }

        if ($this->getHour()) {
            $delay .= " <b>{$this->getHour()}</b>" . ($this->getHour() > 1 ? ' hours' : ' hour');
        }
        if ($this->getMinute()) {
            $delay .= " <b>{$this->getMinute()}</b>" . ($this->getMinute() > 1 ? ' mins' : ' min');
        }

        if (!$this->getDay() && !$this->getHour() && !$this->getMinute()) {
            $delay = 'immediately';
        } else {
            $delay .= ' later';
        }

        $coupon = '';
        if ($this->getCouponEnabled()) {
            $coupon = 'with coupon';
        }

        $crossSellSource = '';
        if ($this->getCrossSellsEnabled() && $this->getCrossSellsTypeId()) {
            $crossSellSource = ' including ' . $this->crossSellSource->toArray()[$this->getCrossSellsTypeId()];
        }

        if ($this->getTemplate()) {
            return __(
                'Send <b>%1</b> email %2 %3 %4',
                $this->getTemplate()->getTitle(),
                $delay,
                $coupon,
                $crossSellSource
            );
        } else {
            return __(
                'No Template Selected - <small>probably the previously used email template has been removed</small>.'
            );
        }
    }
}

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



namespace Mirasvit\Email\Event\Condition;

use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Repository\QueueRepositoryInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Event\Api\Data\Event\InstanceEventInterface;
use Mirasvit\Event\EventData\CustomerData;

class Trigger extends AbstractCondition
{
    const ELEMENT_DAY_NAME = 'day_value';
    const ATTRIBUTE_OPTION_ANY = 'any';
    const OPERATOR_HAS = '>';
    const OPERATOR_DOES_NOT_HAVE = '<=';

    /**
     * @var TriggerRepositoryInterface
     */
    private $triggerRepository;

    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    public function __construct(
        QueueRepositoryInterface $queueRepository,
        TriggerRepositoryInterface $triggerRepository,
        Context $context,
        array $data = []
    ) {
        $this->triggerRepository = $triggerRepository;
        $this->queueRepository = $queueRepository;

        parent::__construct($context, $data);

        $this->setData('type', self::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperatorInputByType()
    {
        return ['string' => ['<=', '>']];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperatorOptions()
    {
        return [
            '<=' => __('does not have'),
            '>'  => __('has'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function loadAttributeOptions()
    {
        $this->setData('attribute_option', [
            self::ATTRIBUTE_OPTION_ANY          => __('Any'),
            QueueInterface::STATUS_PENDING      => __('Ready to go'),
            QueueInterface::STATUS_SENT         => __('Sent'),
            QueueInterface::STATUS_UNSUBSCRIBED => __('Unsubcribed'),
        ]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function loadValueOptions()
    {
        $this->setData('value_option', $this->triggerRepository->getCollection()->toOptionHash());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueElementType()
    {
        return 'multiselect';
    }

    /**
     * Get element for day value input.
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getDayValueElement()
    {
        $elementParams = [
            'name'           => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . ']['
                . self::ELEMENT_DAY_NAME . ']',
            'value'          => $this->getDayValue(),
            'value_name'     => $this->getDayValue() ?: '...',
            'explicit_apply' => false,
            'data-form-part' => $this->getFormName(),
        ];

        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__' . self::ELEMENT_DAY_NAME,
            'text',
            $elementParams
        )
            ->setRenderer($this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class));
    }

    /**
     * {@inheritdoc}
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . __(
                'Recipient %1 emails with %2 status in the Mail Log for the trigger(s) %3 within last %4 days',
                $this->getOperatorElementHtml(),
                $this->getAttributeElementHtml(),
                $this->getValueElementHtml(),
                $this->getDayValueElement()->toHtml()
            )
            . $this->getRemoveLinkHtml();
    }

    /**
     * Get value stored in the day_value element.
     *
     * @return int
     */
    private function getDayValue()
    {
        return (int)$this->getData(self::ELEMENT_DAY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function loadArray($arr)
    {
        if (isset($arr[self::ELEMENT_DAY_NAME])) {
            $this->setData(self::ELEMENT_DAY_NAME, $arr[self::ELEMENT_DAY_NAME]);
        }

        return parent::loadArray($arr);
    }

    /**
     * {@inheritDoc}
     */
    public function asArray(array $arrAttributes = [])
    {
        $out = parent::asArray();
        $out[self::ELEMENT_DAY_NAME] = $this->getDayValue();

        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $queue = $this->queueRepository->getCollection();
        $email = $model->getData(InstanceEventInterface::PARAM_CUSTOMER_EMAIL);
        $status = $this->getData('attribute');
        $operator = $this->getOperator();

        $queue->addFieldToFilter(QueueInterface::RECIPIENT_EMAIL, $email)
            ->addFieldToFilter(QueueInterface::TRIGGER_ID, ['in' => $this->getValue()]);

        if ($status !== self::ATTRIBUTE_OPTION_ANY) {
            $queue->addFieldToFilter(QueueInterface::STATUS, $status);
        }

        if ($this->getDayValue() > 0) {
            $date = date('Y-m-d', strtotime("-{$this->getDayValue()} days"));
            $dateField = $status === QueueInterface::STATUS_SENT
                ? QueueInterface::SENT_AT
                : QueueInterface::SCHEDULED_AT;

            $queue->getSelect()->where("DATE({$dateField}) >= ?", $date);
        }

        $checkSql = $queue->getConnection()->getCheckSql('COUNT(*) ' . $operator . ' 0', 1, 0);

        $queue->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(new \Zend_Db_Expr($checkSql));

        $result = (int)$queue->getConnection()->fetchOne($queue->getSelect());

        return (bool)$result;
    }
}

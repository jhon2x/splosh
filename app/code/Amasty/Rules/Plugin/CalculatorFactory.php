<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin;

/**
 * Create instance of SP calculator by action name.
 *
 * @codingStandardsIgnoreFile
 */
class CalculatorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Amasty\Rules\Helper\Data
     */
    protected $rulesDataHelper;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Amasty\Rules\Helper\Data $rulesDataHelper
    ) {
        $this->_objectManager = $objectManager;
        $this->rulesDataHelper = $rulesDataHelper;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject
     * @param \Closure $proceed
     * @param string $type
     *
     * @return mixed
     */
    public function aroundCreate(
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject,
        \Closure $proceed,
        $type
    ) {
        $rules = $this->rulesDataHelper->getDiscountTypes(true);

        if (isset($rules[$type])) {
            $path = $this->rulesDataHelper->getFilePath($type);

            return $this->_objectManager->create($path);
        } else {
            return $proceed($type);
        }
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Test\Unit\Model\Rule\Action\Discount;

use Amasty\Rules\Model\Rule\Action\Discount\Moneyamount;
use Amasty\Rules\Test\Unit\TestHelper\ObjectCreatorTrait;
use Amasty\Rules\Test\Unit\TestHelper\ReflectionTrait;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class MoneyamountTest
 *
 * @see Moneyamount
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codingStandardsIgnoreFile
 */
class MoneyamountTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectCreatorTrait;

    /**#@+
     * Required data of AbstractRule|Rule object
     */
    const ITEMS_COUNT = 10;
    const RULE_DISCOUNT_STEP = 20;
    const RULE_SIMPLE_ACTION = \Amasty\Rules\Helper\Data::TYPE_AMOUNT;
    const RULE_DISCOUNT_QTY = 0; //Maximum Qty Discount is Applied To
    const RULE_DISCOUNT_AMOUNT = 1;
    /**#@-*/

    protected function setUp()
    {
        $this->initQuote();
    }

    /**
     * Used validateItems function replaced with stub.
     *
     * @covers Moneyamount::_calculate
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testCalculate()
    {
        $total = $this->prepareQuoteItems(false);

        /** @var Moneyamount|MockObject $action */
        $action = $this->getMockBuilder(Moneyamount::class)
            ->disableOriginalConstructor()
            ->setMethods(['validateItems'])
            ->getMock(); // Using mock for replace validateItems method with stub.

        $this->setProperty($action, 'validator', $this->initValidator());
        $action->expects($this->any())->method('validateItems')->will($this->returnValue($this->items));
        $data = $this->getObjectManager()->getObject(\Magento\SalesRule\Model\Rule\Action\Discount\Data::class);
        $this->setProperty($action, 'discountFactory', $this->initDiscountDataFactory($data));

        $discountAmount = 0;

        foreach ($this->items as $item) {
            /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $itemDiscount */
            $itemDiscount = $this->invokeMethod($action, '_calculate', [$this->initRule(), $item]);
            $discountAmount += $itemDiscount->getAmount();
        }

        $this->assertEquals(floor($total / self::RULE_DISCOUNT_STEP) * self::RULE_DISCOUNT_AMOUNT, $discountAmount);
    }
}

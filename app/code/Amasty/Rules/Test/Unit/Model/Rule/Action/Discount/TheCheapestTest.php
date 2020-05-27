<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Test\Unit\Model\Rule\Action\Discount;

use Amasty\Rules\Model\Rule\Action\Discount\Thecheapest;
use Amasty\Rules\Test\Unit\TestHelper\ObjectCreatorTrait;
use Amasty\Rules\Test\Unit\TestHelper\ReflectionTrait;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class TheCheapestCalculationTest
 *
 * @see Thecheapest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codingStandardsIgnoreFile
 */
class TheCheapestTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectCreatorTrait;

    /**#@+
     * Required data of Item object
     */
    const ITEM_PRICE = 100;
    const ITEM_BASE_PRICE = 100;
    const ITEM_ORIGINAL_PRICE = 100;
    const ITEM_BASE_ORIGINAL_PRICE = 100;
    /**#@-*/

    /**#@+
     * Required data of AbstractRule|Rule object
     */
    const ITEMS_COUNT = 10;
    const RULE_DISCOUNT_STEP = 10;
    const RULE_SIMPLE_ACTION = \Amasty\Rules\Helper\Data::TYPE_CHEAPEST;
    const RULE_DISCOUNT_QTY = 0;
    const RULE_DISCOUNT_AMOUNT = 10;
    /**#@-*/

    /**
     * @var \Magento\Quote\Model\Quote\Item\AbstractItem|MockObject
     */
    private $item;

    protected function setUp()
    {
        $this->initQuote();
    }

    /**
     * @covers Thecheapest::calculateDiscount
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testCalculateDiscount()
    {
        $this->item = $this->initQuoteItem();
        $productHelper = $this->initProductHelper();

        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data|MockObject $data */
        $data = $this->createPartialMock(\Magento\SalesRule\Model\Rule\Action\Discount\Data::class, []);
        $dataFactory = $this->initDiscountDataFactory($data);

        /** @var Thecheapest $action */
        $action = $this->getObjectManager()->getObject(
            Thecheapest::class,
            [
                'discountDataFactory' => $dataFactory,
                'rulesProductHelper' => $productHelper,
            ]
        );
        $this->invokeMethod($action, 'calculateDiscount', [$this->item, 1, self::RULE_DISCOUNT_AMOUNT]);

        $this->assertEquals(
            $data->getAmount(),
            self::ITEM_PRICE * self::RULE_DISCOUNT_AMOUNT / 100,
            'Discount calculation: wrong getAmount result.'
        );
        $this->assertEquals(
            $data->getBaseAmount(),
            self::ITEM_BASE_PRICE * self::RULE_DISCOUNT_AMOUNT / 100,
            'Discount calculation: wrong getBaseAmount result.'
        );
        $this->assertEquals(
            $data->getOriginalAmount(),
            self::ITEM_ORIGINAL_PRICE * self::RULE_DISCOUNT_AMOUNT / 100,
            'Discount calculation: wrong getOriginalAmount result.'
        );
        $this->assertEquals(
            $data->getBaseOriginalAmount(),
            self::ITEM_BASE_ORIGINAL_PRICE * self::RULE_DISCOUNT_AMOUNT / 100,
            'Discount calculation: wrong getBaseOriginalAmount result.'
        );
    }

    /**
     * Used validateItems function replaced with stub.
     *
     * @covers \Thecheapest::getAllowedItemsIds
     *
     * @throws \ReflectionException
     */
    public function testGetAllowedItemsIds()
    {
        $qty = $this->prepareQuoteItems();

        /** @var Thecheapest|MockObject $action */
        $action = $this->getMockBuilder(Thecheapest::class)
            ->disableOriginalConstructor()
            ->setMethods(['validateItems'])
            ->getMock();
        $this->setProperty($action, 'validator', $this->initValidator());
        $action->expects($this->any())->method('validateItems')->will($this->returnValue($this->items));

        $items = $this->invokeMethod(
            $action,
            'getAllowedItemsIds',
            [
                $this->address,
                $this->initRule()
            ]
        );

        $this->assertCount(
            intval($qty / static::RULE_DISCOUNT_STEP ?: 1),
            $items,
            'Expected allowed items count mismatch actual'
        );

        /**
         * Use `amrules_id` value of first created test item because of it's smaller than others.
         */
        $firstItem = reset($this->items);
        $this->assertEquals(
            $firstItem->getAmrulesId(),
            reset($items),
            'Field `amrules_id` of first allowed item isn\'t like expected.'
        );
    }

    /**
     * @return \Amasty\Rules\Helper\Product|MockObject
     */
    private function initProductHelper()
    {
        /** @var \Amasty\Rules\Helper\Product|MockObject $productHelper */
        $productHelper = $this->createMock(\Amasty\Rules\Helper\Product::class);
        $productHelper->expects($this->any())
            ->method('getItemPrice')->with($this->item)->will($this->returnValue(self::ITEM_PRICE));
        $productHelper->expects($this->any())
            ->method('getItemBasePrice')->with($this->item)->will($this->returnValue(self::ITEM_BASE_PRICE));
        $productHelper->expects($this->any())
            ->method('getItemOriginalPrice')->with($this->item)->will($this->returnValue(self::ITEM_ORIGINAL_PRICE));
        $productHelper->expects($this->any())
            ->method('getItemBaseOriginalPrice')
            ->with($this->item)
            ->will($this->returnValue(self::ITEM_BASE_ORIGINAL_PRICE));

        return $productHelper;
    }
}

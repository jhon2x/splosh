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



namespace Mirasvit\Email\Helper;

class Quote extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Quote\Model\Quote\Address $quoteAddress,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->quoteAddress    = $quoteAddress;
        $this->quoteFactory    = $quoteFactory;
        $this->storeManager    = $storeManager;
        $this->context         = $context;

        parent::__construct($context);
    }

    /**
     * @param string $email
     * @return \Magento\Quote\Model\Quote
     */
    public function getCartByCapturedEmail($email)
    {
        $orderField = \Magento\Quote\Api\Data\CartInterface::KEY_RESERVED_ORDER_ID;

        $collection = $this->quoteAddress->getCollection();
        $quoteId = (int)$collection->addFieldToFilter('email', $email)
            ->addFieldToFilter('main_table.customer_id', ['null' => ''])
            ->getSelect()->joinInner(
                ['quote' => $collection->getTable('quote')],
                'quote.entity_id = main_table.quote_id AND quote. ' . $orderField . ' IS NULL ',
                []
            )->order(
                'updated_at ' . \Magento\Framework\DB\Select::SQL_DESC
            )->limit(
                1
            )->query()->fetchColumn(1);

        return $this->quoteFactory->create()->setSharedStoreIds(array_keys($this->storeManager->getStores()))
            ->load($quoteId);
    }
}

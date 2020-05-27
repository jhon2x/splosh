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


namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable;

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Quote
{
    /**
     * Constructor
     *
     * @param StoreManagerInterface  $storeManager
     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @param QuoteFactory           $quoteFactory
     * @param Context                $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        QuoteCollectionFactory $quoteCollectionFactory,
        QuoteFactory $quoteFactory,
        Context $context
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
        $this->context = $context;
    }

    /**
     * Quote model
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        $quote = $this->quoteFactory->create();

        if ($this->context->getData('quote')) {
            return $this->context->getData('quote');
        } elseif ($this->context->getData('quote_id')) {
            $quote = $this->quoteFactory->create()
                ->setSharedStoreIds(array_keys($this->storeManager->getStores()))
                ->load($this->context->getData('quote_id'));
        }

        $this->context->setData('quote', $quote);

        return $quote;
    }

    /**
     * Quote model
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getCart()
    {
        return $this->getQuote();
    }

    /**
     * Random variables
     *
     * @return array
     */
    public function getRandomVariables()
    {
        $variables = [];
        $quoteCollection = $this->quoteCollectionFactory->create()
            ->addFieldToFilter('items_qty', ['gt' => 0]);
        if ($quoteCollection->getSize()) {
            $quoteCollection->getSelect()->limit(1, rand(0, $quoteCollection->getSize() - 1));

            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $quoteCollection->getFirstItem();

            if ($quote->getId()) {
                $variables['quote_id'] = $quote->getId();
            }
        }

        return $variables;
    }
}

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


namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable;

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Quote extends AbstractVariable
{
    protected $supportedTypes = ['Magento\Quote\Model\Quote'];

    /**
     * Constructor
     *
     * @param StoreManagerInterface  $storeManager
     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @param QuoteFactory           $quoteFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        QuoteCollectionFactory $quoteCollectionFactory,
        QuoteFactory $quoteFactory
    ) {
        parent::__construct();

        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Quote model.
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if ($this->context->getData('quote')) {
            return $this->context->getData('quote');
        }

        $quote = $this->quoteFactory->create();
        if ($this->context->getData('quote_id')) {
            $quote = $quote->setSharedStoreIds(array_keys($this->storeManager->getStores()))
                ->load($this->context->getData('quote_id'));

            $this->context->setData('quote', $quote);
        }

        return $quote;
    }
}

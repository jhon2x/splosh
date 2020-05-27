<?php
/**
 * Copyright © 2016 Sectionio. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sectionio\Metrics\Model\ResourceModel;

class Settings extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        $this->transactionManager = $context->getTransactionManager();
        $this->_resources = $context->getResources();
        $this->objectRelationProcessor = $context->getObjectRelationProcessor();
        if ($resourcePrefix !== null) {
            $this->_resourcePrefix = $resourcePrefix;
        }
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init(
            'sectionio_settings',
            'general_id'
        );
    }
}

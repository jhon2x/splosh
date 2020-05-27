<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin\CheckoutStaging\Model\ResourceModel;

use Magento\CheckoutStaging\Setup\InstallSchema;

/**
 * Fix Magento issue with table prefix on preview
 */
class PreviewQuotaPlugin
{
    /**
     * @param \Magento\CheckoutStaging\Model\ResourceModel\PreviewQuota $subject
     * @param callable $proceed
     * @param int $id
     *
     * @return bool
     */
    public function aroundInsert(
        \Magento\CheckoutStaging\Model\ResourceModel\PreviewQuota $subject,
        callable $proceed,
        $id
    ) {
        $connection = $subject->getConnection();
        $select = $connection->select()
            ->from($subject->getTable(InstallSchema::PREVIEW_QUOTA_TABLE)) // Amasty fix: added getTable call
            ->where('quote_id = ?', (int) $id);
        if (!empty($connection->fetchRow($select))) {
            return true;
        }
        return 1 === $connection->insert(
            $subject->getTable(InstallSchema::PREVIEW_QUOTA_TABLE),
            ['quote_id' => (int) $id]
        );
    }
}

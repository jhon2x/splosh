<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\ZipCode;

use Magento\Framework\App\ResourceConnection;
use MageWorx\ShippingRules\Helper\Data as Helper;

/**
 * Class AbstractZip
 */
abstract class AbstractZip
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * AbstractZip constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param Helper $helper
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Helper $helper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->helper             = $helper;
    }
}
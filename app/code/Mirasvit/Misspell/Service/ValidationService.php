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
 * @package   mirasvit/module-misspell
 * @version   1.0.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Misspell\Service;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\Core\Service\AbstractValidator;

class ValidationService extends AbstractValidator
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    public function testReindex()
    {
        $select = $this->resource->getConnection()
            ->select()
            ->from($this->resource->getTableName('mst_misspell_index'));

        if ($this->resource->getConnection()->fetchRow($select)) {
            return [self::SUCCESS, __FUNCTION__, []];
        } else {
            return [self::FAILED, __FUNCTION__, ['bin/magento indexer:reindex mst_misspell']];
        }
    }
}

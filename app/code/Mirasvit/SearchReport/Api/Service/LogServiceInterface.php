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
 * @package   mirasvit/module-search-report
 * @version   1.0.5
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchReport\Api\Service;

use Mirasvit\SearchReport\Api\Data\LogInterface;

interface LogServiceInterface
{
    /**
     * @param string $query
     * @param int $results
     * @param string $source
     * @param string $misspellQuery
     * @param string $fallbackQuery
     * @return LogInterface
     */
    public function logQuery($query, $results, $source, $misspellQuery, $fallbackQuery);

    /**
     * @param int $logId
     * @return LogInterface
     */
    public function logClick($logId);
}
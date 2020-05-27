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
 * @package   mirasvit/module-report-api
 * @version   1.0.24
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Config\Type;

use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;

class Percent extends Number implements TypeInterface
{
    public function getType()
    {
        return self::TYPE_PERCENT;
    }

    public function getAggregators()
    {
        return ['none', 'avg'];
    }

    public function getJsType()
    {
        return self::JS_TYPE_PERCENT;
    }

    public function getFormattedValue($actualValue, AggregatorInterface $aggregator)
    {
        if ($actualValue === null) {
            return self::NA;
        }

        return number_format($actualValue, 2) . '%';
    }
}

<?php

namespace Acidgreen\SploshBackorder\Model\Plugin\CatalogImportExport\Import\Product\Type;

use Magento\CatalogImportExport\Model\Import\Product\Type\Simple as SimpleType;

class Simple
{

    public function aroundClearEmptyData(
        SimpleType $type,
        callable $proceed,
        ...$args
    ) {

        if (isset($args[0]['force_backorder'])) {
            $forceBackorderData = $args[0]['force_backorder'];
        }
        $returnValue = $proceed(...$args);

        if (!isset($returnValue['force_backorder']) && isset($forceBackorderData)) 
            $returnValue['force_backorder'] = $forceBackorderData;

        return $returnValue;

    }
}

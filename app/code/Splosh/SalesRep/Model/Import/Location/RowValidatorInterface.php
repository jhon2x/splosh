<?php

namespace Splosh\SalesRep\Model\Import\Location;

interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
    const ERROR_STAFF_ID_IS_EMPTY = 'staffIdEmpty';

    /**
     * @param $context
     * @return mixed
     */
    public function init($context);
}
<?php

namespace Acidgreen\SploshExo\Helper;

use Acidgreen\Exo\Helper\ImportModelObjectInterface;

class ImportModelObject implements ImportModelObjectInterface
{
    /**
     * @var \Acidgreen\SploshExo\Model\Import\StaffFactory
     */
    protected $staffImportModel;

    public function __construct(
        \Acidgreen\SploshExo\Model\Import\StaffFactory $staffImportModel
    ) {
        $this->staffImportModel = $staffImportModel;
    }

    public function getImportModel($processType)
    {
        $model = false;
        if ($processType == 'staff') {
            $model = $this->staffImportModel;
        }
        return $model;
    }
}

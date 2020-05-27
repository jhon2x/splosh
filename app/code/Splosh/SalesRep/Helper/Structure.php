<?php

namespace Splosh\SalesRep\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Structure extends AbstractHelper
{
    const TABLE     = 'splosh_staff_location_mapping';
    const STAFF_TABLE = 'splosh_staff';

    const ID        = 'id';
    const STAFF_ID  = 'staff_id';
    const STATE     = 'state';
    const POSTCODE  = 'postcode';
    const SUBURB    = 'suburb';

    const ID_LABEL          = 'ID';
    const STAFF_LABEL       = 'Sales Rep.';
    const STATES_LABEL      = 'States';
    const POSTCODES_LABEL   = 'Postcodes';
    const SUBURBS_LABEL     = 'Suburbs';

    const STAFF_EXO_ID = 'id';
    const EXO_STAFF_ID = 'exo_staff_id';
    const EXO_STAFF_NAME = 'name';
    const EXO_STAFF_NICKNAME = 'nickname';
    const EXO_STAFF_JOBTITLE = 'jobtitle';
    const EXO_STAFF_EMAIL = 'email';
    const EXO_STAFF_PHONE_NUMBER = 'phone_no';
    const EXO_STAFF_WEBSITE_ID = 'website_id';
    const EXO_STAFF_ACTIVE = 'is_active';
    const EXO_STAFF_PHOTO = 'photo';


    /**
     * @var array
     */
    protected $_columns = [
        self::STAFF_ID,
        self::STATE,
        self::POSTCODE,
        self::SUBURB
    ];

    /**
     * @var array
     */
    protected $_columnsLabels = [
        self::ID_LABEL        => self::ID,
        self::STAFF_LABEL     => self::STAFF_ID,
        self::STATES_LABEL    => self::STATE,
        self::POSTCODES_LABEL => self::POSTCODE,
        self::SUBURBS_LABEL   => self::SUBURB
    ];

    /**
     * @var array
     */
    protected $_staffColumns = [
        self::STAFF_EXO_ID,
        self::EXO_STAFF_ID,
        self::EXO_STAFF_NAME,
        self::EXO_STAFF_NICKNAME,
        self::EXO_STAFF_JOBTITLE,
        self::EXO_STAFF_EMAIL,
        self::EXO_STAFF_PHONE_NUMBER,
        self::EXO_STAFF_WEBSITE_ID,
        self::EXO_STAFF_ACTIVE,
        //self::EXO_STAFF_PHOTO
    ];

    /**
     * @var array
     */
    protected $_staffColumnLabels = [
        self::STAFF_EXO_ID      => 'Staff EXO ID',
        self::EXO_STAFF_ID      => 'Staff Id',
        self::EXO_STAFF_NAME    => 'Name',
        self::EXO_STAFF_NICKNAME => 'Nickname',
        self::EXO_STAFF_JOBTITLE => 'Job Title',
        self::EXO_STAFF_EMAIL   => 'Email',
        self::EXO_STAFF_PHONE_NUMBER => 'Phone Number',
        self::EXO_STAFF_WEBSITE_ID => 'Website Id',
        self::EXO_STAFF_ACTIVE  => 'Is Active',
        self::EXO_STAFF_PHOTO   => 'Photo'
    ];

    /**
     * Structure constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->_columns;
    }

    /**
     * @return array
     */
    public function getStaffColumns()
    {
        return $this->_staffColumns;
    }

    /**
     * @return array
     */
    public function getStaffColumnsLabels()
    {
        return $this->_staffColumnLabels;
    }
}
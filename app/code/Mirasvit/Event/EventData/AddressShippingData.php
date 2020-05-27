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
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\EventData;

use Magento\Framework\DataObject;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\Api\Service\OptionsConverterInterface;
use Mirasvit\Event\EventData\Condition\AddressShippingCondition;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;

class AddressShippingData extends DataObject implements EventDataInterface
{
    const IDENTIFIER = 'shipping';

    const ID = 'shipping_address_id';
    /**
     * @var CountryCollectionFactory
     */
    private $countryCollectionFactory;
    /**
     * @var RegionCollectionFactory
     */
    private $regionCollectionFactory;
    /**
     * @var OptionsConverterInterface
     */
    private $optionsConverter;

    public function __construct(
        OptionsConverterInterface $optionsConverter,
        CountryCollectionFactory $countryCollectionFactory,
        RegionCollectionFactory $regionCollectionFactory,
        array $data = []
    ) {
        $this->optionsConverter = $optionsConverter;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;

        parent::__construct($data);
    }

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function getConditionClass()
    {
        return AddressShippingCondition::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return __('Shipping Address');
    }

    /**v
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        $countries = $this->optionsConverter->convert($this->countryCollectionFactory->create()->toOptionArray());
        $regions = $this->optionsConverter->convert($this->regionCollectionFactory->create()->toOptionArray());

        return [
            'country_id' => [
                'label'   => __('Country'),
                'type'    => self::ATTRIBUTE_TYPE_ENUM_MULTI,
                'options' => $countries,
            ],
            'city' => [
                'label' => __('City'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'region_id' => [
                'label'   => __('State/Province'),
                'type'    => self::ATTRIBUTE_TYPE_ENUM_MULTI,
                'options' => $regions,
            ],
            'region' => [
                'label' => __('Region'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'postcode' => [
                'label' => __('Postcode'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
        ];
    }
}

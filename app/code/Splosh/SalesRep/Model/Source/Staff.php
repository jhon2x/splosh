<?php

namespace Splosh\SalesRep\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Acidgreen\SploshExo\Model\ResourceModel\Staff\CollectionFactory as StaffCollection;
use Magento\Store\Api\WebsiteRepositoryInterface;

class Staff implements OptionSourceInterface
{
    /**
     * @var StaffCollection
     */
    protected $staffCollection;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $website;

    /**
     * Staff constructor.
     * @param StaffCollection $staffCollection
     * @param WebsiteRepositoryInterface $website
     */
    public function __construct(
        StaffCollection $staffCollection,
        WebsiteRepositoryInterface $website
    )
    {
        $this->staffCollection = $staffCollection->create();
        $this->website = $website;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function toOptionArray()
    {
        $result = [];
        $result[] = ['label' => 'Select Staff', 'value' => '0'];
        foreach ($this->staffCollection as $item => $value) {
            $result[] = [
                'label' => $value['name'] . ' (' . $this->getWebsiteLabel($value['website_id']) . ')',
                'value' => $value['id']
            ];
        }

        return $result;
    }

    /**
     * @param $website_id
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getWebsiteLabel($website_id)
    {
        $website = $this->website->getById($website_id);
        return $website->getName();
    }
}
<?php


namespace Acidgreen\SploshExo\Model\Config\Source;

class MageCustomerGroups implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Groups
     *
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $groupCollection;

    /**
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $groupCollection
     */
    public function __construct(\Magento\Customer\Model\ResourceModel\Group\Collection $groupCollection)
    {
        $this->groupCollection = $groupCollection;
    }

    /**
     * Options array
     *
     * @var array
     */
    protected $options;

    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->groupCollection->loadData()->toOptionArray();
        }

        $options = $this->options;

        return $options;
    }
}

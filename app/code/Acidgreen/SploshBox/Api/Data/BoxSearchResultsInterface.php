<?php


namespace Acidgreen\SploshBox\Api\Data;

interface BoxSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get box list.
     * @return \Acidgreen\SploshBox\Api\Data\BoxInterface[]
     */
    
    public function getItems();

    /**
     * Set box_type list.
     * @param \Acidgreen\SploshBox\Api\Data\BoxInterface[] $items
     * @return $this
     */
    
    public function setItems(array $items);
}

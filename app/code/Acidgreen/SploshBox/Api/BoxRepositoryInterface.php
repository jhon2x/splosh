<?php


namespace Acidgreen\SploshBox\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface BoxRepositoryInterface
{


    /**
     * Save box
     * @param \Acidgreen\SploshBox\Api\Data\BoxInterface $box
     * @return \Acidgreen\SploshBox\Api\Data\BoxInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function save(
        \Acidgreen\SploshBox\Api\Data\BoxInterface $box
    );

    /**
     * Create an instance of a Box model
     * @return \Acidgreen\SploshBox\Model\Box\
     */
    public function create();

    /**
     * Retrieve box
     * @param string $boxId
     * @return \Acidgreen\SploshBox\Api\Data\BoxInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function getById($boxId);

    /**
     * Retrieve box matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Acidgreen\SploshBox\Api\Data\BoxSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete box
     * @param \Acidgreen\SploshBox\Api\Data\BoxInterface $box
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function delete(
        \Acidgreen\SploshBox\Api\Data\BoxInterface $box
    );

    /**
     * Delete box by ID
     * @param string $boxId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function deleteById($boxId);
}

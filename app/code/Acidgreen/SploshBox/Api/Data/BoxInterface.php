<?php


namespace Acidgreen\SploshBox\Api\Data;

interface BoxInterface
{

    const MULTI_QTY = 'multi_qty';
    const BOX_ID = 'box_id';
    const BOX_TYPE = 'box_type';
    const IS_ACTIVE = 'is_active';


    /**
     * Get box_id
     * @return string|null
     */
    
    public function getBoxId();

    /**
     * Set box_id
     * @param string $box_id
     * @return Acidgreen\SploshBox\Api\Data\BoxInterface
     */
    
    public function setBoxId($boxId);

    /**
     * Get box_type
     * @return string|null
     */
    
    public function getBoxType();

    /**
     * Set box_type
     * @param string $box_type
     * @return Acidgreen\SploshBox\Api\Data\BoxInterface
     */
    
    public function setBoxType($box_type);

    /**
     * Get multi_qty
     * @return string|null
     */
    
    public function getMultiQty();

    /**
     * Set multi_qty
     * @param string $multi_qty
     * @return Acidgreen\SploshBox\Api\Data\BoxInterface
     */
    
    public function setMultiQty($multi_qty);
    
    public function setIsActive($isActive);
    
    public function getIsActive();
}

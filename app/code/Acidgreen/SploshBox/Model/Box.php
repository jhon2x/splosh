<?php


namespace Acidgreen\SploshBox\Model;

use Acidgreen\SploshBox\Api\Data\BoxInterface;

class Box extends \Magento\Framework\Model\AbstractModel implements BoxInterface
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Acidgreen\SploshBox\Model\ResourceModel\Box');
    }

    /**
     * Get box_id
     * @return string
     */
    public function getBoxId()
    {
        return $this->getData(self::BOX_ID);
    }

    /**
     * Set box_id
     * @param string $boxId
     * @return Acidgreen\SploshBox\Api\Data\BoxInterface
     */
    public function setBoxId($boxId)
    {
        return $this->setData(self::BOX_ID, $boxId);
    }

    /**
     * Get box_type
     * @return string
     */
    public function getBoxType()
    {
        return $this->getData(self::BOX_TYPE);
    }

    /**
     * Set box_type
     * @param string $box_type
     * @return Acidgreen\SploshBox\Api\Data\BoxInterface
     */
    public function setBoxType($box_type)
    {
        return $this->setData(self::BOX_TYPE, $box_type);
    }

    /**
     * Get multi_qty
     * @return string
     */
    public function getMultiQty()
    {
        return $this->getData(self::MULTI_QTY);
    }

    /**
     * Set multi_qty
     * @param string $multi_qty
     * @return Acidgreen\SploshBox\Api\Data\BoxInterface
     */
    public function setMultiQty($multi_qty)
    {
        return $this->setData(self::MULTI_QTY, $multi_qty);
    }
    
    public function getIsActive()
    {
    	return $this->getData();
    }
    
    public function setIsActive($isActive)
    {
    	$isActive = ($isActive == 'Y') ? true : false;
    	return $this->setData(self::IS_ACTIVE, $isActive);
    }
}

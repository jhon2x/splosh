<?php


namespace Acidgreen\CustomerRestrictions\Model\Config\Source;

use Magento\Eav\Api\AttributeRepositoryInterface;

class ProductRanges extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $_options;

    protected $attributeRepository;

    protected $logger;

    /**
     * Constructor
     *
     * @param Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param array $options
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        \Psr\Log\LoggerInterface $LoggerInterface
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->logger = $LoggerInterface;
    }

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {   
        try{
            $attribute = $this->attributeRepository->get("catalog_product", "range");
            
        } catch(\Magento\Framework\Exception\NoSuchEntityException $ex){
            $this->logger->debug("Please add the range attribute for Product");
        }

        $options = ($attribute) ? $attribute->getOptions() : [];

        if ($this->_options === null) {
            $this->_options = [];

            if($options && !empty($options)){
                foreach ($options as $option) {
                    if($option["value"] != '')
                        $this->_options[] = ['value' => $option["value"], 'label' => __($option["label"])];
                }
            }
        }

        return $this->_options;
    }
}
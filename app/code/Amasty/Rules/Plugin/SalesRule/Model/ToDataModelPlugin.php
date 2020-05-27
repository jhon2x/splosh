<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin\SalesRule\Model;

/**
 * ee21 compatibility (fix magento fatal)
 */
class ToDataModelPlugin
{
    /**
     * @var \Magento\SalesRule\Api\Data\RuleExtensionFactory
     */
    private $extensionFactory;

    public function __construct(\Magento\SalesRule\Api\Data\RuleExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * ee21 compatibility (fix magento fatal)
     *
     * @param \Magento\SalesRule\Model\Converter\ToDataModel $ruleModel
     * @param \Magento\SalesRule\Model\Data\Rule $dataModel
     *
     * @return \Magento\SalesRule\Model\Data\Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToDataModel(\Magento\SalesRule\Model\Converter\ToDataModel $ruleModel, $dataModel)
    {
        $attributes = $dataModel->getExtensionAttributes();

        if (is_array($attributes)) {
            /** @var \Magento\SalesRule\Api\Data\RuleExtensionInterface $attributes */
            $attributes = $this->extensionFactory->create(['data' => $attributes]);
            $dataModel->setExtensionAttributes($attributes);
        }

        return $dataModel;
    }
}

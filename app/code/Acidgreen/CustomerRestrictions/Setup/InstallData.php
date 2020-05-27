<?php


namespace Acidgreen\CustomerRestrictions\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class InstallData implements InstallDataInterface
{

	private $eavSetupFactory;

	private $customerSetupFactory;

	private $attributeSetFactory;

	/**
	 * Constructor
	 *
	 * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
	 * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
	 * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory
	 */
	public function __construct(
		EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
	) {
		$this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function install(
		ModuleDataSetupInterface $setup,
		ModuleContextInterface $context
	) {
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

		$eavSetup->addAttribute(
			\Magento\Customer\Model\Customer::ENTITY,
			'range_restrictions',
			[
				'type' => 'varchar',
				'label' => 'Restricted Range',
				'input' => 'multiselect',
				'source' => 'Acidgreen\CustomerRestrictions\Model\Config\Source\ProductRanges',
				'required'  => false,
				'default'   => '',
				'visible' => true,
            	'position' => 300,
				'system'    => false,
				'is_user_defined' => false,
				'is_used_in_grid' => true,
				'is_visible_in_grid' => true,
				'is_filterable_in_grid' => false,
				'is_searchable_in_grid' => false,
				'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend'
			]
		);

		$eavSetup->addAttribute(
			\Magento\Customer\Model\Customer::ENTITY,
			'category_restrictions',
			[
				'type' => 'varchar',
				'label' => 'Restricted Category ID\'s',
				'input' => 'text',
				'required'  => false,
				'default'   => '',
				'visible' => true,
            	'position' => 400,
				'system'    => false,
				'is_user_defined' => true,
				'is_used_in_grid' => false,
				'is_visible_in_grid' => false,
				'is_filterable_in_grid' => false,
				'is_searchable_in_grid' => false,
			]
		);

		$customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

		$customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
		$attributeSetId = $customerEntity->getDefaultAttributeSetId();

		$attributeSet = $this->attributeSetFactory->create();
		$attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

		$range_attribute = $customerSetup->getEavConfig()->getAttribute(
			\Magento\Customer\Model\Customer::ENTITY,
			'range_restrictions'
			)->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer']
			]);

		$range_attribute->save();

		$categ_attribute = $customerSetup->getEavConfig()->getAttribute(
			\Magento\Customer\Model\Customer::ENTITY,
			'category_restrictions'
			)->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'used_in_forms' => ['adminhtml_customer']
			]);
			
		$categ_attribute->save();
	}
}

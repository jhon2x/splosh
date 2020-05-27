<?php

namespace Acidgreen\SploshExo\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Catalog\Model\Product as ProductModel;

use Acidgreen\SploshExo\Helper\Api\Config as ConfigHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

use Acidgreen\SploshExo\Model\CartonSpecs;


class UpgradeData implements UpgradeDataInterface
{

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var CartonSpecs
     */
    private $cartonSpecs;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

	/**
     * @var \Psr\Log\LoggerInterface
     */
	protected $logger;

    /**
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
		AttributeSetFactory $attributeSetFactory,
        CartonSpecs $cartonSpecs,
    	ScopeConfigInterface $scopeConfig,
		\Psr\Log\LoggerInterface $logger
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;

		$this->cartonSpecs = $cartonSpecs;

		$this->scopeConfig = $scopeConfig;

		$this->logger = $logger;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
    	$setup->startSetup();


    	if (version_compare($context->getVersion(), '0.0.2') < 0) {
	    	$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

	    	$eavSetup->removeAttribute(
	    		ProductModel::ENTITY,
	    		'exo_mixed_box'
	    	);

	    	// @todo: remove exo_mixed_box attribute datas as well in catalog_product_entity_int
    	}

    	if (version_compare($context->getVersion(), '0.0.3') < 0) {
    		// populate acidgreen_box table
    		$this->cartonSpecs->startSync();

    		$this->logger->debug(__METHOD__.' :: Please check acidgreen_box table if data was entered. Thank you.');

    		/**
    		 * @var Magento\Eav\Setup\EavSetup
    		 */
    		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
    		$eavSetup->updateAttribute(
    			ProductModel::ENTITY,
    			'exo_ctn_size',
    			[
    				'filterable' => true,
    				'searchable' => true,
    			]
    		);
    	}

        // Somewhere, somehow, this custom product attribute was lost, so we reinstate it using the updateAttribute codes from 0.0.3
    	if (version_compare($context->getVersion(), '0.0.4') < 0) {

    		/**
    		 * @var Magento\Eav\Setup\EavSetup
    		 */
    		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'exo_ctn_size',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend_class' => '',
                    'label' => 'Carton Size (EXO)',
                    'input' => 'select',
                    'class' => '',
                    'source' => 'Acidgreen\SploshBox\Model\Config\Source\Box',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'apply_to' => '',
                    'unique' => false
                ]
            );

    		$eavSetup->updateAttribute(
    			ProductModel::ENTITY,
    			'exo_ctn_size',
    			[
    				'filterable' => true,
    				'searchable' => true,
    			]
    		);

            $this->logger->info(__METHOD__.' :: Please check if exo_ctn_size was indeed added. Thank you.');
        }
    	if (version_compare($context->getVersion(), '0.0.5') < 0) {
    		$b2bWebsiteCodes = $this->scopeConfig->getValue(ConfigHelper::CONFIG_B2B_WEBSITE_CODES);

    		$b2bWebsiteCodes = explode(',', $b2bWebsiteCodes);

    		$connection = $setup->getConnection();

    		$exoProcessTable = $setup->getTable('exo_process');

    		if ($connection->isTableExists($exoProcessTable)) {
    			$data = [
                    'process_id'    => null,
                    'process_name'  => 'Staff Synchronisation',
                    'process_type'  => 'staff',
                    'status'        => 'completed',
                    'progress'      => '100',
    				'is_active'		=> 1,
    			];

                foreach ($b2bWebsiteCodes as $website) {
                    $data['websites'] = $website;
                    $connection->insert($exoProcessTable, $data);

                    $this->logger->info(__METHOD__.' :: Please check if exo_process staff sync was added for '.$website.'. Thank you.');
                }
    		}
        }

        if (version_compare($context->getVersion(), '0.0.6') < 0) {

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'salesperson',
                [
                    'type'      => 'varchar',
                    'label'     => 'Salesperson',
                    'input'     => 'select',
                    'source'    => 'Acidgreen\SploshExo\Model\Config\Source\Staff',
                    'required'  => false,
                    'default'   => ''
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.7') < 0) {

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'display_salesrep',
                [
                    'type'      => 'int',
                    'label'     => 'Display Sales Rep',
                    'input'     => 'select',
                    'source'    => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'required'  => false,
                    'default'   => 0
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.8') < 0) {

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'exo_balance',
                [
                    'type'      => 'varchar',
                    'label'     => 'Exo Account Balance',
                    'input'     => 'text',
                    'required'  => false,
                    'default'   => '',
                    'filterable' => false
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.9') < 0) {
        	/** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'exo_due_date');
            
            $eavSetup->addAttribute(
            	\Magento\Catalog\Model\Product::ENTITY, 
            	'exo_due_date', 
            	[
            		'type' => 'varchar',
            		'label' => 'EXO Due Date',
                	'scope' => 'website',
            		'input' => 'text',
            		'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
            		'required' => false,
            		'default' => '',
            		'filterable' => false
            	]);
            
            
            $this->logger->info(__METHOD__.' :: Please double check exo_due_date at eav_attribute table and at Magento Catalog admin.');
        }
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $sql = "UPDATE exo_process SET is_active = 0 WHERE process_type = ? AND websites IN (?, ?)";
            $statement = $setup->getConnection()
                        ->query($sql, [
                            \Acidgreen\Exo\Model\Process::PROCESS_TYPE_CUSTOMER,
                            'base',
                            'b2c_nz_web'
                        ]);
            $result = $statement->execute();
            $this->logger->info('SPL-466 :: Please double check if B2C customer syncs were disabled (process_type customer). :: '.print_r($result, true).' -- '.__METHOD__);
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $customerGroupData = [
                [
                    'mage_group_id' => 2,
                    'exo_group_id' => '0',
                    'description' => 'Stockists'
                ],
                [
                    'mage_group_id' => 2,
                    'exo_group_id' => '5',
                    'description' => 'Independent Gold'
                ],
                [
                    'mage_group_id' => 2,
                    'exo_group_id' => '6',
                    'description' => 'Independent Silver'
                ],
                [
                    'mage_group_id' => 2,
                    'exo_group_id' => '7',
                    'description' => 'Independent Bronze'
                ],
                [
                    'mage_group_id' => 2,
                    'exo_group_id' => '8',
                    'description' => 'Independent Royal'
                ],
                [
                    'mage_group_id' => 2,
                    'exo_group_id' => '9',
                    'description' => 'Corporate'
                ],
                [
                    'mage_group_id' => 2,
                    'exo_group_id' => '10',
                    'description' => 'Independent Platinum'
                ]
            ];

            $customerGroupTable = $setup->getTable('splosh_customer_group');

            $connection = $setup->getConnection();

            foreach ($customerGroupData as $customerGroup) {
                $this->logger->info(print_r($connection->isTableExists($customerGroupTable), true));
                $this->logger->info(__METHOD__);
                if ($connection->isTableExists($customerGroupTable)) {
                    $connection->insert($customerGroupTable, $customerGroup);
                }
            }
        }
    }
}

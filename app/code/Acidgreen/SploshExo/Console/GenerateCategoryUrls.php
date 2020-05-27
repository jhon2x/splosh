<?php

namespace Acidgreen\SploshExo\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;

class GenerateCategoryUrls extends Command
{
	/**
	 * @var CategoryCollectionFactory
	 */	
	protected $categoryCollectionFactory;
	
	/**
	 * @var CategoryUrlRewriteGenerator
	 */
	protected $categoryUrlRewriteGenerator;
	
	/**
	 * @var StoreManagerInterface
	 */
	protected $storeManager;
	
	/**
	 * @var InputInterface
	 */
	private $input;
	
	/**
	 * @var OutputInterface
	 */
	private $output;
	
	/**
	 * @var LoggerInterface
	 */
	protected $logger;
	
	public function __construct(
		CategoryCollectionFactory $categoryCollectionFactory,
		CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
		StoreManagerInterface $storeManager,
		LoggerInterface $logger
	) {
		$this->categoryCollectionFactory = $categoryCollectionFactory;
		$this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
		$this->storeManager = $storeManager;
		$this->logger = $logger;
		parent::__construct();
	}
	
	protected function configure()
	{
		$this->setName('sploshexo:category_urls:generate')
			->setDescription('Re-generate Category URLs')
			->setDefinition([
				new InputOption(
					'category-id',
					'-i',
					InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
					'If specified, generate only category URLs for this category ID.'
				),
				new InputOption('store-id',
					'-s',
					InputOption::VALUE_REQUIRED,
					'Store ID to use'
				),
				new InputOption('parent-category-id',
					'-p',
					InputOption::VALUE_REQUIRED,
					'Parent Category ID attached to the category/categories you want to process.'
				)
			]);
	}
	
	protected function execute(
		InputInterface $input,
		OutputInterface $output
	) {
		$this->input = $input;
		$category_ids = $this->input->getOption('category-id');
		$store_id = $this->input->getOption('store-id');
		$parent_id = $this->input->getOption('parent-category-id');
		
		$this->storeManager->setCurrentStore($store_id);

		$collection = $this->categoryCollectionFactory->create();
		// $collection->addAttributeToSelect('*');
		$collection->addAttributeToSelect('name');
		$collection->addFieldToFilter('parent_id', $parent_id);
		if (!empty($category_ids)) {
			$collection->addFieldToFilter('entity_id', ['in' => $category_ids]);
		} else {
			$collection->addFieldToFilter('level', '2'); // the one you see first on the menus...
		}
		
		
		
		if (count($collection) > 0) {
			foreach ($collection as $category) {
				try {
					$category->save();
					$this->categoryUrlRewriteGenerator->generate($category);
					$output->write('Please check if SEO-friendly url for '.$category->getId(). ' was generated.');
					
				} catch (Magento\Framework\Exception\AlreadyExistsException $e) {
					$this->logger->error($e->getTraceAsString());
					$this->logger->error($e->getMessage());
				}
			}
		}
	}
}
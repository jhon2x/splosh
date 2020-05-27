<?php

namespace Acidgreen\SploshBackorder\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Magento\Catalog\Model\ProductFactory;

class SetForceBackorder extends Command
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
        parent::__construct();
        $state->setAreaCode('adminhtml');
    }
	
	protected function configure()
    {
		$this->setName('splosh:backorder:force_backorder')
			->setDescription('Add force_backorder value under store_id = 0')
			->setDefinition([
				new InputOption(
					'store-id',
					'-s',
					InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
					'If specified, set force_backorder using the supplied store_id'
				),
				new InputOption(
					'product-id',
					'-p',
					InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
					'If specified, set force_backorder = 0 for the product IDs'
				),
            ]);
    }

	protected function execute(
		InputInterface $input,
		OutputInterface $output
	) {
        $productIds = $input->getOption('product-id');
        $collection = $this->productFactory->create()->getCollection();
        $collection->addAttributeToSelect(['force_backorder']);
         $output->writeln('Setting force_backorder to 0 under store_id 0...');

        $i = 0;
        if (!empty($productIds)) {
            $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);
        }

        foreach ($collection as $product) {
            $resource = $product->getResource();
            $product->setStoreId(0);
            $product->setData('force_backorder', 0);
            $resource->saveAttribute($product, 'force_backorder');
            $output->writeln('Please check if product '.$product->getId(). ' was updated.');
            $i++;
        }
    }

}

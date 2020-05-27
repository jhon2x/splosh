<?php

namespace Acidgreen\SploshCatalogData\Console;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Acidgreen\SploshCatalogData\Model\Category as CategorySetup;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Add categories thru this utility
 */
class Addcategory extends Command
{
	const FIXTURE = 'add-fixture';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CategorySetup
     */
    protected $categorySetup;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
	
    public function __construct(
    	CategorySetup $categorySetup,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
	) {
    	$this->categorySetup = $categorySetup;
    	$this->_storeManager = $storeManager;
	    $this->logger = $logger;	
	    parent::__construct();
	}
	
	protected function configure()
	{
		$this->setName('sploshcatalog:addcategory')
		     ->setDescription('Add Categories')
		     ->setDefinition([
		     	new InputOption(
		     		self::FIXTURE,
		     		'-f',
		     		InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
		     		'If specified, it will be included at the list of file/s to use in importing Categories',
                    []
		     	)
		     ]);
	}
	
	protected function execute(
		InputInterface $input,
		OutputInterface $output
	) {
        $this->input = $input;
        $output->writeln('Add categories via this command: php bin/magento --add-fixture=<Namespace_Module>::fixtures/<filename>');

		$fixtures = $this->input->getOption('add-fixture');
		
		if (!empty($fixtures)) {
			$this->categorySetup->install($fixtures);
			$output->writeln('Please check if categories were created.');
		}
	}
}

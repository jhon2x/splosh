<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Console\Command;

use Mirasvit\Search\Api\Repository\StopwordRepositoryInterface;
use Mirasvit\Search\Api\Service\StopwordServiceInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Store\Model\StoreManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class StopwordCommand extends Command
{
    /**
     * @var StopwordRepositoryInterface
     */
    private $repository;

    /**
     * @var StopwordServiceInterface
     */
    private $service;

    /**
     * @var StoreManager
     */
    private $storeManager;

    public function __construct(
        StopwordRepositoryInterface $stopwordRepository,
        StopwordServiceInterface $stopwordService,
        StoreManager $storeManager
    ) {
        $this->repository = $stopwordRepository;
        $this->service = $stopwordService;
        $this->storeManager = $storeManager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'Stopwords file'
            ),
            new InputOption(
                'store',
                null,
                InputOption::VALUE_REQUIRED,
                'Store Id'
            ),
            new InputOption(
                'remove',
                null,
                InputOption::VALUE_NONE,
                'remove'
            ),
        ];

        $this->setName('mirasvit:search:stopword')
            ->setDescription('Import stopwords')
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('remove')) {
            $store = $input->getOption('store');

            $collection = $this->repository->getCollection();
            if ($store) {
                $collection->addFieldToFilter('store_id', $store);
            }

            $cnt = 0;
            foreach ($collection as $item) {
                $this->repository->delete($item);
                $cnt++;

                if ($cnt % 1000 == 0) {
                    $output->writeln("<info>$cnt stopwords are removed...</info>");
                }
            }

            $output->writeln("<info>$cnt stopwords are removed.</info>");

            return;
        }

        if ($input->getOption('file') && $input->getOption('store')) {
            $file = $input->getOption('file');
            $store = $input->getOption('store');

            $generator = $this->service->import($file, $store);
            $first = $generator->current();

            $progress = new ProgressBar($output, $first['stopwords']);
            $progress->start();

            foreach ($generator as $result) {
                $progress->advance(1);
            }
            $progress->finish();

            $output->writeln("");
            $output->writeln("<info>Imported {$result['stopwords']} stopwords</info>");
        }
    }
}

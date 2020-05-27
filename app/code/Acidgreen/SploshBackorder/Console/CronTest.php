<?php

namespace Acidgreen\SploshBackorder\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronTest extends Command
{
	
	protected function configure()
    {
		$this->setName('splosh:backorder:crontest')
			->setDescription('Test an EXO CRON')
			->setDefinition([
				new InputOption(
					'process-id',
					'-p',
					InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
					'If specified, exo_process w/ that process_id will be set to Pending.'
				),
				new InputOption(
					'run-cron',
					'-r',
					InputOption::VALUE_OPTIONAL,
					'If specified, run the cron on the spot.'
				),
            ]);
    }

	protected function execute(
		InputInterface $input,
		OutputInterface $output
	) {
        $processIds = $input->getOption('process-id');
        $runCron = $input->getOption('run-cron');
    }
}

<?php
/**
 * Copyright © 2017 Sectionio. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Sectionio\Metrics\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GreetingCommand
 */
class UpdateVclCommand extends Command
{
    /** @var \Sectionio\Metrics\Helper\Aperture $aperture */
    protected $aperture;
    /** @var \Sectionio\Metrics\Helper\State $state */
    protected $state;
    /** @var \Magento\PageCache\Model\Config\PageCache $pageCacheConfig */
    protected $pageCacheConfig;
    /** @var \Sectionio\Metrics\Helper\Data $helper */
    protected $helper;

    /**
     * @param \Sectionio\Metrics\Helper\Aperture $aperture
     * @param \Sectionio\Metrics\Helper\State $state
     * @param \Sectionio\Metrics\Helper\Data $helper
     * @param \Magento\PageCache\Model\Config $pageCacheConfig
     */
    public function __construct(
        \Sectionio\Metrics\Helper\Aperture $aperture,
        \Sectionio\Metrics\Helper\State $state,
        \Sectionio\Metrics\Helper\Data $helper,
        \Magento\PageCache\Model\Config $pageCacheConfig
    ) {
        parent::__construct();
        $this->aperture = $aperture;
        $this->state = $state;
        $this->pageCacheConfig = $pageCacheConfig;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sectionio:updatevcl')
            ->setDescription('Update Varnish with the Magento VCL');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $account_id = $this->state->getAccountId();
        $application_id = $this->state->getApplicationId();
        $environment_name = $this->state->getEnvironmentName();
        $proxy_name = $this->state->getProxyName();
        /** @var string $proxy_image*/
        $proxy_version = $this->helper->getProxyVersion($account_id, $application_id);

        if (!$account_id) {
            throw new \Exception('account_id has not been set, please run sectionio:setup');
        }

        if (!$application_id) {
            throw new \Exception('application_id has not been set, please run sectionio:setup');
        }

        if (!$environment_name) {
            throw new \Exception('environment_name has not been set, please run sectionio:setup');
        }

        if (!$proxy_name) {
            throw new \Exception('proxy_name has not been set, please run sectionio:setup');
        }

        $major_release = $this->helper->getMajorRelease($proxy_version);


        /** Extract the generated VCL code appropriate for their version*/
        $vcl = $this->helper->getCorrectVCL($this->pageCacheConfig, $major_release);

        $result = $this->aperture->updateProxyConfiguration($account_id, $application_id, $environment_name, $proxy_name, $vcl, 'MagentoTurpentine');

        if ($result['http_code'] == 200) {
            $output->writeln('You have successfully updated varnish configuration.');
        } else {
            $output->writeln('Error updating varnish configuration, upstream returned HTTP ' . $result['http_code'] . '.');
        }
    }
}

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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Repository;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Dir;
use Magento\Framework\Setup\SampleData\FixtureManager;
use Mirasvit\Core\Service\YamlService;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Repository\CampaignRepositoryInterface;
use Mirasvit\Email\Api\Repository\CampaignTemplateRepositoryInterface;
use Mirasvit\Email\Api\Repository\Trigger\ChainRepositoryInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;
use Mirasvit\EmailDesigner\Api\Repository\TemplateRepositoryInterface;

class CampaignTemplateRepository implements OptionSourceInterface, CampaignTemplateRepositoryInterface
{
    const CAMPAIGN_PATH = '/Setup/data/campaign/';
    const TRIGGER_PATH  = '/Setup/data/trigger/';
    const TEMPLATE_PATH = '/Setup/data/template/';
    const FIXTURE_EXT   = '.yaml';

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $moduleReader;

    /**
     * @var Collection
     */
    private $campaignCollection;
    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;
    /**
     * @var FixtureManager
     */
    private $fixtureManager;
    /**
     * @var ChainRepositoryInterface
     */
    private $chainRepository;
    /**
     * @var TriggerRepositoryInterface
     */
    private $triggerRepository;
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

    public function __construct(
        TemplateRepositoryInterface $templateRepository,
        ChainRepositoryInterface $chainRepository,
        TriggerRepositoryInterface $triggerRepository,
        FixtureManager $fixtureManager,
        Collection $collection,
        CampaignRepositoryInterface $campaignRepository,
        \Magento\Framework\Module\Dir\Reader $moduleReader
    ) {
        $this->campaignCollection = $collection;
        $this->campaignRepository = $campaignRepository;
        $this->moduleReader = $moduleReader;
        $this->fixtureManager = $fixtureManager;
        $this->chainRepository = $chainRepository;
        $this->triggerRepository = $triggerRepository;
        $this->templateRepository = $templateRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options   = [];
        $campaigns = $this->getCollection();

        foreach ($campaigns as $campaign) {
            $options[] = [
                'value'       => $campaign->getId(),
                'label'       => $campaign->getTitle(),
                'description' => $campaign->getDescription()
            ];
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        if ($this->campaignCollection->getSize()) {
            return $this->campaignCollection;
        }

        $dir = dirname($this->moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Mirasvit_Email')).self::CAMPAIGN_PATH;
        foreach (array_diff(scandir($dir), ['..', '.']) as $fileName) {
            $filePath = $dir . $fileName;

            $data = YamlService::parse($filePath);

            $campaign = $this->campaignRepository->create();
            $campaign->setId($fileName);
            $campaign->addData($data);

            $this->campaignCollection->addItem($campaign);
        }

        return $this->campaignCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if ($this->campaignCollection->getItemById($id)) {
            return $this->campaignCollection->getItemById($id);
        }

        $item = $this->getCollection()->getItemById($id);
        if (!$item === null) {
            throw new LocalizedException(__('Campaign Template with ID "%1" does not exist', $id));
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function create($templateId)
    {
        $template = $this->get($templateId);
        $campaign = $this->campaignRepository->create();

        // create campaign
        $campaign->addData($template->getData());
        $campaign->unsetData(CampaignInterface::ID);
        $this->campaignRepository->save($campaign);

        // create triggers
        foreach ($template->getData('triggers') as $triggerId) {
            $fixtureName = 'Mirasvit_Email::'.self::TRIGGER_PATH.$triggerId.self::FIXTURE_EXT;
            $filename    = $this->fixtureManager->getFixture($fixtureName);
            $data        = YamlService::parse($filename);

            $trigger = $this->triggerRepository->create();
            $trigger->addData($data);
            $trigger->setCampaignId($campaign->getId());
            $this->triggerRepository->save($trigger);

            // create email chains
            foreach ($trigger->getData('chain') as $chainData) {
                $chain = $this->chainRepository->create();
                $chain->addData($chainData);
                $this->setChainTemplate($chain);
                $chain->setTriggerId($trigger->getId());
                $this->chainRepository->save($chain);
            }
        }

        return $campaign;
    }

    /**
     * Set template ID for chain, create email template if needed.
     *
     * @param ChainInterface $chain
     */
    private function setChainTemplate(ChainInterface $chain)
    {
        $systemId = $chain->getTemplateId(); // we store system_id as the number in the template file name
        $templates = $this->templateRepository->getCollection();
        $templates->addFieldToFilter(TemplateInterface::SYSTEM_ID, $systemId);

        if ($templates->getSize()) { // template with given system ID exists - use it
            /** @var TemplateInterface $template */
            $template = $templates->getFirstItem();
            $chain->setTemplateId($template->getId());
        } else { // fallback to a template with the same name
            $dir = dirname($this->moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Mirasvit_Email'))
                .self::TEMPLATE_PATH;

            // iterate through all system templates
            foreach (array_diff(scandir($dir), ['..', '.']) as $fileName) {
                $parts = explode('_', $fileName);
                $templateId = (int) array_shift($parts);
                if ($templateId === $systemId) {
                    $filePath = $dir . $fileName;

                    $data = YamlService::parse($filePath);
                    $templates = $this->templateRepository->getCollection();
                    $templates->addFieldToFilter(TemplateInterface::TITLE, $data['title']);

                    if ($templates->getSize()) {// template with the same name exists - use it
                        $template = $templates->getFirstItem();
                        $chain->setTemplateId($template->getId());
                    } else { // finally, if there are no similar templates - create it from template
                        $template = $this->templateRepository->create()->import($filePath);
                        $chain->setTemplateId($template->getId());
                    }

                    break;
                }
            }
        }
    }
}

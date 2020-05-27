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


namespace Mirasvit\Email\Setup;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\EmailDesigner\Model\ThemeFactory;
use Mirasvit\EmailDesigner\Model\TemplateFactory;
use Mirasvit\Core\Service\YamlService as YamlParser;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var ThemeFactory
     */
    protected $themeFactory;

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;
    /**
     * @var YamlParser
     */
    private $yamlParser;
    /**
     * @var TriggerRepositoryInterface
     */
    private $triggerRepository;

    /**
     * @var State
     */
    private $appState;

    public function __construct(
        State $appState,
        TriggerRepositoryInterface $triggerRepository,
        YamlParser $yamlParser,
        ThemeFactory $themeFactory,
        TemplateFactory $templateFactory
    ) {
        $this->appState = $appState;
        $this->themeFactory = $themeFactory;
        $this->templateFactory = $templateFactory;
        $this->yamlParser = $yamlParser;
        $this->triggerRepository = $triggerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $this->appState->setAreaCode(Area::AREA_GLOBAL);
        } catch (\Exception $e) {
        }

        $this->installTemplates();
    }

    /**
     * Install default theme and templates.
     */
    private function installTemplates()
    {
        $themePath = dirname(__FILE__) . '/data/theme/';
        foreach (scandir($themePath) as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
            $this->themeFactory->create()->import($themePath . $file);
        }

        $templatePath = dirname(__FILE__) . '/data/template/';
        foreach (scandir($templatePath) as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
            $this->templateFactory->create()->import($templatePath . $file);
        }
    }
}

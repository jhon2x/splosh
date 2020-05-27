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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailDesigner\Controller\Adminhtml\Theme;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\EmailDesigner\Api\Repository\TemplateRepositoryInterface;
use Mirasvit\EmailDesigner\Api\Repository\ThemeRepositoryInterface;
use Mirasvit\EmailDesigner\Api\Service\TemplateProcessorInterface;
use Mirasvit\EmailDesigner\Controller\Adminhtml\Theme;
use Mirasvit\EmailDesigner\Api\Data\ThemeInterface;
use \Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable\Pool as VariablePool;

class Drop extends Theme
{
    /**
     * @var TemplateProcessorInterface
     */
    private $templateProcessor;
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

    public function __construct(
        TemplateProcessorInterface $templateProcessor,
        TemplateRepositoryInterface $templateRepository,
        ThemeRepositoryInterface $themeRepository,
        Registry $registry,
        Context $context
    ) {
        $this->templateProcessor = $templateProcessor;
        $this->templateRepository = $templateRepository;

        parent::__construct($themeRepository, $registry, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->initModel();

        if (strpos($_SERVER['HTTP_HOST'], 'm2.mirasvit.com') === false) {
            foreach ($this->getRequest()->getParam(ThemeInterface::THEME_AREAS) as $key => $value) {
                $model->setDataUsingMethod($key, $value);
            }
        }

        /** @var VariablePool $variablePool */
        $variablePool = $this->_objectManager->get(VariablePool::class);
        $variables = $variablePool->getRandomVariables();
        $variables['preview'] = true;

        try {
            $this->getResponse()->setBody($this->templateProcessor->processTemplate($this->createTemplate($model), $variables));
        } catch (\Exception $e) {
            $message = '<div style="margin: 10px 0px;padding:12px;color: #D8000C;background-color: #FFD2D2;">'
                . $e->getMessage()
                . '</div>';

            $this->getResponse()->setBody($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function _processUrlKeys()
    {
        return true;
    }

    /**
     * @param ThemeInterface $model
     *
     * @return \Mirasvit\EmailDesigner\Api\Data\TemplateInterface
     */
    private function createTemplate(ThemeInterface $model)
    {
        $template = $this->templateRepository->create();
        $template->setTheme($model)
            ->setTemplateAreas([]);

        return $template;
    }
}

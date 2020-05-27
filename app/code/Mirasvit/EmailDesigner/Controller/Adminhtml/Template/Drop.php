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



namespace Mirasvit\EmailDesigner\Controller\Adminhtml\Template;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\EmailDesigner\Api\Repository\TemplateRepositoryInterface;
use Mirasvit\EmailDesigner\Api\Service\TemplateProcessorInterface;
use Mirasvit\EmailDesigner\Controller\Adminhtml\Template;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable\Pool as VariablePool;
use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;

class Drop extends Template
{
    /**
     * @var VariablePool
     */
    protected $variablePool;
    /**
     * @var TemplateProcessorInterface
     */
    private $templateProcessor;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        TemplateProcessorInterface $templateProcessor,
        VariablePool $variablePool,
        TemplateRepositoryInterface $templateRepository,
        Registry $registry,
        Context $context
    ) {
        $this->variablePool = $variablePool;
        $this->templateProcessor = $templateProcessor;

        parent::__construct($templateRepository, $registry, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->initModel();

        if ($this->getRequest()->getParam(TemplateInterface::TEMPLATE_AREAS)
            && strpos($_SERVER['HTTP_HOST'], 'm2.mirasvit.com') === false
        ) {
            foreach ($this->getRequest()->getParam(TemplateInterface::TEMPLATE_AREAS) as $key => $value) {
                $model->setAreaText($key, $value);
            }
        }

        $variables = $this->variablePool->getRandomVariables();
        $variables['preview'] = true;

        try {
            $this->getResponse()->setBody($this->templateProcessor->processTemplate($model, $variables));
        } catch (\Exception $e) {
            $message = '<div style="margin: 10px 0px;padding:12px;color: #D8000C;background-color: #FFD2D2;">'
                . $e->getMessage()
                . '</div>';

            $this->getResponse()->setBody($message);
        }
    }
}

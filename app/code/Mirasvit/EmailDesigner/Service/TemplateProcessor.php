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



namespace Mirasvit\EmailDesigner\Service;


use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;
use Mirasvit\EmailDesigner\Api\Service\TemplateEngineInterface;
use Mirasvit\EmailDesigner\Api\Service\TemplateProcessorInterface;
use Magento\Framework\Translate\Inline\StateInterface;

class TemplateProcessor implements TemplateProcessorInterface
{
    /**
     * @var array|\Mirasvit\EmailDesigner\Api\Service\TemplateEngineInterface[]
     */
    private $templateEngines;
    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * TemplateProcessor constructor.
     *
     * @param StateInterface            $inlineTranslation
     * @param TemplateEngineInterface[] $templateEngines
     */
    public function __construct(StateInterface $inlineTranslation, array $templateEngines = [])
    {
        $this->templateEngines = $templateEngines;
        $this->inlineTranslation= $inlineTranslation;
    }

    /**
     * {@inheritdoc}
     */
    public function processTemplate(TemplateInterface $template, array $variables = [])
    {
        return $this->process($template, $template->getTemplateText(), $variables);
    }

    /**
     * {@inheritdoc}
     */
    public function processSubject(TemplateInterface $template, array $variables = [])
    {
        return $this->process($template, $template->getTemplateSubject(), $variables);
    }

    /**
     * Process template.
     *
     * @param TemplateInterface $template  - template model
     * @param string            $tpl       - template text
     * @param array             $variables - variables for the template
     *
     * @return string
     */
    public function process($template, $tpl, array $variables = [])
    {
        $variables['template_id'] = $template->getId();

        // for native templates use Magento template engine in first order
        $templateEngines = $this->templateEngines;
        if ($template instanceof \Mirasvit\EmailDesigner\Model\Email\Template) {
            uasort($templateEngines, [$this, 'sortEngines']);
        }

        // set template areas as variables
        foreach ($template->getTemplateAreas() as $code => $text) {
            $variables['area_' . $code] = $text;
        }

        // temporary suspend inline translation status to avoid wrong content in email templates }}}
        $this->inlineTranslation->suspend();

        foreach ($templateEngines as $templateEngine) {
            $tpl = $templateEngine->render($tpl, $variables);
        }

        $this->inlineTranslation->resume();

        return $tpl;
    }

    /**
     * Move the Magento template engine on first position.
     *
     * @param TemplateEngineInterface $a
     * @param TemplateEngineInterface $b
     *
     * @return int
     */
    public function sortEngines($a, $b)
    {
        return strpos(get_class($a), 'Magento') !== false ? -1 : 1;
    }
}

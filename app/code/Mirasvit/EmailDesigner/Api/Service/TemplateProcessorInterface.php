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



namespace Mirasvit\EmailDesigner\Api\Service;


use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;

interface TemplateProcessorInterface
{
    /**
     * Process template with given template text.
     *
     * @param TemplateInterface $template  - template model
     * @param string            $tpl       - template text
     * @param array             $variables - variables for the template
     *
     * @return string
     */
    public function process($template, $tpl, array $variables = []);

    /**
     * Process template.
     *
     * @param TemplateInterface $template  - template model
     * @param array             $variables - variables for the template
     *
     * @return string
     */
    public function processTemplate(TemplateInterface $template, array $variables = []);

    /**
     * Process subject.
     *
     * @param TemplateInterface $template  - template model
     * @param array             $variables - variables for the template
     *
     * @return string
     */
    public function processSubject(TemplateInterface $template, array $variables = []);
}

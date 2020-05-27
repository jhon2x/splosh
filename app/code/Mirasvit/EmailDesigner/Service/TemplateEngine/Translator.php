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



namespace Mirasvit\EmailDesigner\Service\TemplateEngine;


use Mirasvit\EmailDesigner\Api\Service\TemplateEngineInterface;

class Translator implements TemplateEngineInterface
{
    /**
     * Translate email template text.
     * Text translated string by string.
     *
     * {@inheritdoc}
     */
    public function render($template, array $variables = [])
    {
        $translatedStrings = [];
        $strings = explode("\n", $template);

        foreach ($strings as $idx => $string) {
            $searchString = trim($string);
            $trans = __($searchString)->render();

            $translatedStrings[] = str_replace($searchString, $trans, $string);
        }

        return implode($translatedStrings, "\n");
    }
}

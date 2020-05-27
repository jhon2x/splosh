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



namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Filter;


class StringFilter
{
    /**
     * If Empty
     *
     * @param string $input
     * @param string $default
     *
     * @return string
     */
    public function if_empty($input, $default)
    {
        if (!$input || $input == '') {
            return $default;
        }

        return $input;
    }
}
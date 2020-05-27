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
 * @package   mirasvit/module-email-report
 * @version   2.0.8
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailReport\Api\Service;

interface CookieInterface
{
    /**
     * Get value key cookie.
     *
     * @param string $name
     *
     * @return string
     */
    public function get($name);

    /**
     * Set cookie.
     *
     * @param string $name
     * @param string $value
     * @param int $duration
     *
     * @return void
     */
    public function set($name, $value, $duration = 3600);
}

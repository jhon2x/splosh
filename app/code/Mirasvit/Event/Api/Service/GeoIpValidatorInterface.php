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
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Api\Service;


interface GeoIpValidatorInterface
{
    /**
     * Check, whether the asserted continent not equals to the client's continent.
     *
     * @param string      $continent - continent code
     * @param null|string $targetIp  - IP, over which the test should be run
     *
     * @return bool
     */
    public function assertContinentNotEquals($continent, $targetIp = null);
}

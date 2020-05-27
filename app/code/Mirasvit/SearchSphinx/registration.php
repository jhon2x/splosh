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
 * @package   mirasvit/module-search-sphinx
 * @version   1.1.49
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


// @codingStandardsIgnoreStart
$configFile = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/app/etc/autocomplete.json';
//extension may be installed by coping files to the folder /app/code/Mirasvit
//file_exists may not work if open_basedir restriction in effect. 
if (!file_exists($configFile)) { 
    $configFile = dirname(dirname(dirname(dirname(__DIR__)))) . '/app/etc/autocomplete.json';
}
if (isset($_SERVER)
    && is_array($_SERVER)
    && isset($_SERVER['REQUEST_URI'])
    && strpos($_SERVER['REQUEST_URI'], 'searchautocomplete/ajax/suggest') !== false
    && file_exists($configFile)) { //file_exists may not work if open_basedir restriction in effect.
    require_once 'autocomplete.php';
}
$registration = dirname(dirname(dirname(__DIR__)))
    . '/vendor/mirasvit/module-search-sphinx/src/SearchSphinx/registration.php';
if (file_exists($registration)) {//file_exists may not work if open_basedir restriction in effect. 
    # module was already installed via composer
    return;
}
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Mirasvit_SearchSphinx',
    __DIR__
);
// @codingStandardsIgnoreEnd

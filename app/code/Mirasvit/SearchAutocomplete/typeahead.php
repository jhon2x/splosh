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
 * @package   mirasvit/module-search-autocomplete
 * @version   1.1.96
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


// phpcs:ignoreFile
namespace Mirasvit\SearchAutocomplete;

if (php_sapi_name() == "cli") {
    return;
}

$configFile = dirname(dirname(dirname(__DIR__))) . '/etc/typeahead.json';

if (stripos(__DIR__, 'vendor') !== false) {
    $configFile = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/app/etc/typeahead.json';
}

if (!file_exists($configFile)) {
    return \Zend_Json::encode([]);
}

$config = \Zend_Json::decode(file_get_contents($configFile));

class TypeAheadAutocomplete
{
    private $config;

    public function __construct(
        array $config
    ) {
        $this->config = $config;
    }

    public function process()
    {
        $query = $this->getQueryText();
        $query = substr($query, 0, 2);
        return isset($this->config[$query])?$this->config[$query]:'';
    }

    private function getQueryText()
    {
        return filter_input(INPUT_GET, 'q') !== null ? filter_input(INPUT_GET, 'q') : '';
    }
}

$result = (new TypeAheadAutocomplete($config))->process();

//s start
exit(\Zend_Json::encode($result));
//s end
/** m start
return \Zend_Json::encode($result);
m end */ 

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



namespace Mirasvit\SearchAutocomplete\Plugin\Framework\UrlFactory;

use Magento\Framework\Url;

class UrlBuilderPlugin
{
    private $url;

    public function __construct(
        Url $url
    ) {
        $this->url = $url;
    }

    public function afterCreate($subject, $result)
    {
        if (php_sapi_name() === 'cli') {
            return $this->url;
        }

        return $result;
    }
}
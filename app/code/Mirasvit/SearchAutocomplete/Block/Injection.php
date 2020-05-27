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



namespace Mirasvit\SearchAutocomplete\Block;

use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Helper\Data as SearchHelper;
use Mirasvit\SearchAutocomplete\Model\Config;

class Injection extends Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * @var SearchHelper
     */
    protected $searchHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param FormatInterface $localeFormat
     * @param SearchHelper $searchHelper
     */
    public function __construct(
        Context $context,
        Config $config,
        FormatInterface $localeFormat,
        SearchHelper $searchHelper
    ) {
        $this->storeManager = $context->getStoreManager();
        $this->config = $config;
        $this->localeFormat = $localeFormat;
        $this->searchHelper = $searchHelper;

        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getJsConfig()
    {
        return [
            'query'              => $this->searchHelper->getEscapedQueryText(),
            'priceFormat'        => $this->localeFormat->getPriceFormat(),
            'minSearchLength'    => $this->config->getMinChars(),
            'url'                => $this->getUrl(
                'searchautocomplete/ajax/suggest',
                ['_secure' => $this->getRequest()->isSecure()]
            ),
            'storeId'            => $this->storeManager->getStore()->getId(),
            'delay'              => $this->config->getDelay(),
            'layout'             => $this->config->getAutocompleteLayout(),
            'popularTitle'       => __('Hot Searches')->render(),
            'popularSearches'    => $this->config->isShowPopularSearches() ? $this->config->getPopularSearches() : [],
            'isTypeaheadEnabled' => $this->config->isTypeAheadEnabled(),
            'typeaheadUrl'       => $this->getUrl(
                'searchautocomplete/ajax/typeahead',
                ['_secure' => $this->getRequest()->isSecure()]
            ),
        ];
    }

    //    /**
    //     * Js configuration array for autocomplete
    //     *
    //     * @return array
    //     */
    //    public function getJsConfig2()
    //    {
    //        $config = [
    //            "*" => [
    //                'Magento_Ui/js/core/app' => [
    //                    'components' => [
    //                        'autocompleteInjection'  => [
    //                            'component' => 'Mirasvit_SearchAutocomplete/js/injection',
    //                            'config'    => [],
    //                        ],
    //                        'autocomplete'           => [
    //                            'component' => 'Mirasvit_SearchAutocomplete/js/autocomplete',
    //                            'provider'  => 'autocompleteProvider',
    //                            'config'    => [
    //                                'query'           => $this->searchHelper->getEscapedQueryText(),
    //                                'priceFormat'     => ,
    //                                'minSearchLength' => ,
    //                            ],
    //                        ],
    //                        'autocompleteProvider'   => [
    //                            'component' => 'Mirasvit_SearchAutocomplete/js/provider',
    //                            'config'    => [
    //
    //                            ],
    //                        ],
    //                        'autocompleteNavigation' => [
    //                            'component'    => 'Mirasvit_SearchAutocomplete/js/navigation',
    //                            'autocomplete' => 'autocomplete',
    //                        ],
    //                    ],
    //                ],
    //            ],
    //        ];
    //
    //        if ($this->config->isShowPopularSearches()) {
    //            $config['*']['Magento_Ui/js/core/app']['components']['autocompletePopular'] = [
    //                'component'    => 'Mirasvit_SearchAutocomplete/js/popular',
    //                'autocomplete' => 'autocomplete',
    //                'provider'     => 'autocompleteProvider',
    //                'config'       => [
    //                    'enabled'         => $this->config->isShowPopularSearches(),
    //                    'queries'         => $this->config->getPopularSearches(),
    //                    'minSearchLength' => $this->config->getMinChars(),
    //                ],
    //            ];
    //        } else {
    //            $config['*']['Magento_Ui/js/core/app']['components']['autocompleteRecent'] = [
    //                'component'    => 'Mirasvit_SearchAutocomplete/js/recent',
    //                'autocomplete' => 'autocomplete',
    //                'provider'     => 'autocompleteProvider',
    //                'config'       => [
    //                    'limit'           => 5,
    //                    'minSearchLength' => $this->config->getMinChars(),
    //                ],
    //            ];
    //        }
    //        return $config;
    //    }

    /**
     * @return string
     */
    public function getCssStyles()
    {
        return $this->config->getCssStyles();
    }
}

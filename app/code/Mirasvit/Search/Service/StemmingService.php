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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Service;

use Mirasvit\Search\Api\Service\Stemming\StemmerInterface;
use Mirasvit\Search\Api\Service\StemmingServiceInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;

class StemmingService implements StemmingServiceInterface
{
    /**
     * @var StemmerInterface[]
     */
    private $stemmers;

    /**
     * @var LocaleResolver
     */
    private $localeResolver;

    public function __construct(
        LocaleResolver $localeResolver,
        array $stemmers = []
    ) {
        $this->localeResolver = $localeResolver;
        $this->stemmers = $stemmers;
    }

    /**
     * {@inheritdoc}
     */
    public function singularize($string)
    {
        // string is too short
        if (strlen($string) < 3) {
            return $string;
        }

        $locale = strtolower(explode('_', $this->localeResolver->getLocale())[0]);

        if (array_key_exists($locale, $this->stemmers)) {
            return $this->stemmers[$locale]->singularize($string);
        }

        return $string;
    }
}

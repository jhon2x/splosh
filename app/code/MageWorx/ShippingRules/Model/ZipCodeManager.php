<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use MageWorx\ShippingRules\Model\ZipCode\AlphaNumericZip;
use MageWorx\ShippingRules\Model\ZipCode\AlphaNumericZipNL;
use MageWorx\ShippingRules\Model\ZipCode\AlphaNumericZipUK;
use MageWorx\ShippingRules\Model\ZipCode\NumericZip;
use MageWorx\ShippingRules\Model\ZipCode\AlphaNumericZipUKOutcode;

/**
 * Class ZipCodeManager
 *
 * Stores all available formats of zip code and works like a factory for formatters,
 * which could be found in the \MageWorx\ShippingRules\Model\ZipCode namespace
 *
 */
class ZipCodeManager
{
    /**
     * Alphanumeric and Numeric is required base formatters!
     * Other formatters are optional and could be not used.
     */
    const ZIP_FORMATS = [
        self::NUMERIC_FORMAT         => 'MageWorx\ShippingRules\Model\ZipCode\NumericZip',
        self::ALPHANUMERIC_FORMAT    => 'MageWorx\ShippingRules\Model\ZipCode\AlphaNumericZip',
        self::ALPHANUMERIC_FORMAT_UK => 'MageWorx\ShippingRules\Model\ZipCode\AlphaNumericZipUK',
        self::ALPHANUMERIC_FORMAT_NL => 'MageWorx\ShippingRules\Model\ZipCode\AlphaNumericZipNL',
    ];

    /**
     * Keys for base formatters
     */
    const NUMERIC_FORMAT         = 'numeric';
    const ALPHANUMERIC_FORMAT    = 'alphanumeric';
    const ALPHANUMERIC_FORMAT_UK = 'alphanumeric_uk';
    const ALPHANUMERIC_FORMAT_NL = 'alphanumeric_nl';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \MageWorx\ShippingRules\Api\ZipCodeFormatInterface[]
     */
    private $formatters = [];

    /**
     * List of detected formats by zip code
     *
     * @var array
     */
    private $detectedFormats = [];

    /**
     * ZipCodeManager constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Detects a valid formatter for zip code, which instance will be returned on success.
     *
     * @important Throws an exception in case of failure (incorrect or not-supported format)
     *
     * @param string|int $zip
     * @return \MageWorx\ShippingRules\Api\ZipCodeFormatInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function detectFormatter($zip)
    {
        $format    = $this->detectFormat($zip);
        $formatter = $this->getFormatter($format);

        return $formatter;
    }

    /**
     * Detects format of a zip code using all available formatters and
     * constant alphanumeric and numeric formatters
     *
     * @param string|int $zip
     * @return string
     */
    public function detectFormat($zip)
    {
        if (!empty($this->detectedFormats[$zip])) {
            return $this->detectedFormats[$zip];
        }

        $formatters = $this->getAllFormatters();
        $format     = null;

        foreach ($formatters as $name => $formatter) {
            if ($name == self::NUMERIC_FORMAT || $name == self::ALPHANUMERIC_FORMAT) {
                continue;
            }

            if ($formatter->isSuitableZip($zip)) {
                $format = $name;
                break;
            }
        }

        if (!$format && $formatters[self::ALPHANUMERIC_FORMAT]->isSuitableZip($zip)) {
            $format = self::ALPHANUMERIC_FORMAT;
        }

        $this->detectedFormats[$zip] = $format ? $format : self::NUMERIC_FORMAT;

        return $this->detectedFormats[$zip];
    }

    /**
     * Returns an instances of all available formatters.
     *
     * @return \MageWorx\ShippingRules\Api\ZipCodeFormatInterface[]
     */
    public function getAllFormatters()
    {
        if (!empty($this->formatters)) {
            return $this->formatters;
        }

        $formatters = [];
        foreach (static::ZIP_FORMATS as $name => $className) {
            $formatters[$name] = $this->objectManager->get($className);
        }

        $this->formatters = $formatters;

        return $this->formatters;
    }

    /**
     * Get formatter according required format
     *
     * @param string $format
     * @return \MageWorx\ShippingRules\Api\ZipCodeFormatInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getFormatter($format)
    {
        $formatters = $this->getAllFormatters();
        $this->validateFormat($format);

        return $formatters[$format];
    }

    /**
     * Does validation according format.
     *
     * @important Throws exception in case an error found.
     *
     * @param string $format
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function validateFormat($format)
    {
        $formatters = $this->getAllFormatters();

        if (!is_string($format)) {
            throw new LocalizedException(__('Invalid input type %1, string required', gettype($format)));
        }

        if (empty($formatters[$format])) {
            throw new NoSuchEntityException(__('Unable to locate formatter for the %1 format', $format));
        }
    }

    /**
     * Returns a table alias according the format.
     *
     * @param string $zipFormat
     * @return string
     * @throws LocalizedException
     */
    public function getTableAlias($zipFormat)
    {
        $this->validateFormat($zipFormat);
        switch ($zipFormat) {
            case static::NUMERIC_FORMAT:
                return NumericZip::TABLE_ALIAS;
            case static::ALPHANUMERIC_FORMAT:
                return AlphaNumericZip::TABLE_ALIAS;
            case static::ALPHANUMERIC_FORMAT_UK:
                return AlphaNumericZipUK::TABLE_ALIAS;
            case static::ALPHANUMERIC_FORMAT_NL:
                return AlphaNumericZipNL::TABLE_ALIAS;
            default:
                throw new LocalizedException(__('You missed something when added new format!'));
        }
    }
}
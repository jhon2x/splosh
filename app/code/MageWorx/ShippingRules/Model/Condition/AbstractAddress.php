<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Condition;

use Magento\SalesRule\Model\Rule\Condition\Address as SalesRuleAddress;

/**
 * Class AbstractAddress
 *
 * @method AbstractAddress setAttributeOption(array $array)
 * @method string getAttribute()
 * @method array getAttributeOption()
 * @method bool hasValueParsed()
 * @method AbstractAddress setValueParsed($value)
 */
class AbstractAddress extends SalesRuleAddress
{
    const WILDCARD_SYMBOL = '%';
    const ANY_CHAR_SYMBOL = '?';

    const POST_CODE_PARTS_LIMIT   = 20;
    const POST_CODE_ATTRIBUTES    = [
        'postcode',
    ];
    const UK_POST_CODE_ATTRIBUTES = [
        'uk_incode',
        'uk_outcode',
        'uk_area',
        'uk_district',
        'uk_sector',
        'uk_unit'
    ];

    /**
     * Scalar operators used for the comparison purposes
     *
     * @var array
     */
    protected $scalarOperators = [
        '<=',
        '>',
        '>=',
        '<'
    ];

    /**
     * @var \MageWorx\ShippingRules\Helper\Data
     */
    protected $helper;

    /**
     * @param \MageWorx\ShippingRules\Helper\Data $helper
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $directoryCountry
     * @param \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion
     * @param \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods
     * @param \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods
     * @param array $data
     */
    public function __construct(
        \MageWorx\ShippingRules\Helper\Data $helper,
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion,
        \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods,
        \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $directoryCountry,
            $directoryAllregion,
            $shippingAllmethods,
            $paymentAllmethods,
            $data
        );
    }

    /**
     * Validate product attribute value for condition
     *
     * @param   object|array|int|string|float|bool $validatedValue product attribute value
     *
     * @return  bool
     */
    public function validateAttribute($validatedValue)
    {
        if ($this->isPostcodeAttribute() &&
            $this->helper->isAdvancedPostCodeValidationEnabled()
        ) {
            return $this->getIsValidPostCodeAdvanced($validatedValue);
        }

        return parent::validateAttribute($validatedValue);
    }

    /**
     * Check is postcode attribute validating
     *
     * @return bool
     */
    private function isPostcodeAttribute()
    {
        $postcodeAttributes = array_merge(static::POST_CODE_ATTRIBUTES, static::UK_POST_CODE_ATTRIBUTES);

        return in_array(
            $this->getAttribute(),
            $postcodeAttributes
        );
    }

    /**
     * Validate postcode attribute value for condition
     *
     * @param   object|array|int|string|float|bool $enteredValue product attribute value
     *
     * @return  bool
     */
    private function getIsValidPostCodeAdvanced($enteredValue)
    {
        if (is_object($enteredValue)) {
            return false;
        }

        $desiredPart = $this->getValueParsed();
        $operator    = $this->getOperatorForValidate();

        // if operator requires array and it is not, or on opposite, return false
        if ($this->isArrayOperatorType() xor is_array($desiredPart)) {
            return false;
        }

        // If operator is scalar and value is not scalar return false
        if ($this->isScalarOperator() && !$this->isScalarValue($enteredValue)) {
            return false;
        }

        // Result is false by default
        $result = false;

        switch ($operator) {
            case '==':
            case '!=':
                $result = $this->_compareValues($enteredValue, $desiredPart);
                break;

            case '<=':
            case '>':
                $result = $this->extendedPostcodeComparison($enteredValue, $desiredPart, '<=');
                break;

            case '>=':
            case '<':
                $result = $this->extendedPostcodeComparison($enteredValue, $desiredPart, '>=');
                break;

            case '{}':
            case '!{}':
                if ($this->isScalarValue($enteredValue) && is_array($desiredPart)) {
                    foreach ($desiredPart as $item) {
                        if (stripos($enteredValue, (string)$item) !== false) {
                            $result = true;
                            break;
                        }
                    }
                } elseif (is_array($desiredPart)) {
                    if (is_array($enteredValue)) {
                        $result = array_intersect($desiredPart, $enteredValue);
                        $result = !empty($result);
                    } else {
                        return false;
                    }
                } else {
                    if (is_array($enteredValue)) {
                        $result = in_array($desiredPart, $enteredValue);
                    } else {
                        $result = $this->_compareValues($desiredPart, $enteredValue, false);
                    }
                }
                break;

            case '()':
            case '!()':
                if (is_array($enteredValue)) {
                    $result = count(array_intersect($enteredValue, (array)$desiredPart)) > 0;
                } else {
                    $desiredPart = (array)$desiredPart;
                    foreach ($desiredPart as $item) {
                        if ($this->_compareValues($enteredValue, $item)) {
                            $result = true;
                            break;
                        }
                    }
                }
                break;
        }

        if (in_array($operator, ['!=', '>', '<', '!{}', '!()'])) {
            $result = !$result;
        }

        return $result;
    }

    /**
     * Retrieve parsed value
     *
     * @return array|string|int|float
     */
    public function getValueParsed()
    {
        if (!$this->hasValueParsed()) {
            $value = $this->getData('value');
            if (is_array($value) && isset($value[0]) && is_string($value[0])) {
                foreach ($value as &$item) {
                    if (is_string($item)) {
                        $item = mb_strtoupper($item);
                    }
                }
                $this->setValueParsed($value);
                $this->setData('is_value_parsed', true);

                return $value;
            }
            if ($this->isArrayOperatorType() && $value) {
                $value = preg_split('#\s*[,;]\s*#', $value, null, PREG_SPLIT_NO_EMPTY);
                foreach ($value as &$item) {
                    if (is_string($item)) {
                        $item = mb_strtoupper($item);
                    }
                }
            }
            $this->setValueParsed($value);
        }

        $valueParsed = $this->getData('value_parsed');
        if (is_string($valueParsed)) {
            $valueParsed = mb_strtoupper($valueParsed);
        }

        return $valueParsed;
    }

    /**
     * Check currently used operator: is it scalar?
     *
     * @return bool
     */
    private function isScalarOperator()
    {
        $operator = $this->getOperatorForValidate();

        return in_array($operator, $this->getScalarOperators());
    }

    /**
     * Get all available scalar operators
     *
     * @return array
     */
    public function getScalarOperators()
    {
        return $this->scalarOperators;
    }

    /**
     * Check: value is scalar or not?
     *
     * @param mixed $value
     *
     * @return bool
     */
    private function isScalarValue($value)
    {
        return is_scalar($value);
    }

    /**
     * Case and type insensitive comparison of values
     *
     * @param string|int|float $validatedValue
     * @param string|int|float $desiredPart
     * @param bool $strict
     *
     * @return bool
     */
    protected function _compareValues($validatedValue, $desiredPart, $strict = true)
    {
        $validatedValue = mb_strtoupper($validatedValue);
        if ($this->isPostcodeAttribute() && $this->specialSymbolsFound($desiredPart)) {
            return $this->validatePostcode($validatedValue, $desiredPart, $strict);
        }

        return parent::_compareValues($validatedValue, $desiredPart, $strict);
    }

    /**
     * Check is special symbols was found in the string
     *
     * @param string $string
     *
     * @return bool
     */
    private function specialSymbolsFound($string)
    {
        return stripos($string, static::WILDCARD_SYMBOL) !== false ||
            stripos($string, static::ANY_CHAR_SYMBOL) !== false;
    }

    /**
     * Validate postcode using wildcard
     *
     * @param string $validatedValue
     * @param string $desiredPart
     * @param bool|true $strict
     *
     * @return bool
     */
    protected function validatePostcode($validatedValue, $desiredPart, $strict = true)
    {
        $validatePattern = preg_quote($desiredPart, '~');
        $validatePattern = str_ireplace('\\?', '?', $validatePattern);
        $validatePattern = str_ireplace(static::WILDCARD_SYMBOL, '(.)+', $validatePattern);
        $validatePattern = str_ireplace(static::ANY_CHAR_SYMBOL, '(.){1,1}', $validatePattern);
        if ($strict) {
            $validatePattern = '^' . $validatePattern . '$';
        }
        $result = (bool)preg_match('~' . $validatePattern . '~iu', $validatedValue);

        return $result;
    }

    /**
     * @param string $enteredValue
     * @param string $desiredPart
     * @param string $operator
     *
     * @return bool
     * @throws \Exception
     */
    private function extendedPostcodeComparison($enteredValue, $desiredPart, $operator)
    {
        $partsEnteredValue = $this->explodeStringByAlphaDigits($enteredValue);
        $partsDesired      = $this->explodeStringByAlphaDigits($desiredPart);

        if (count($partsDesired) > static::POST_CODE_PARTS_LIMIT) {
            throw new \Exception(__('Something goes wrong during a post code parsing process.'));
        }

        $isValid = false;
        $i       = -1;
        while ($i++ < count($partsDesired)) {
            if (!isset($partsEnteredValue[$i]) && isset($partsDesired[$i])) {
                // End of validation: entered value is invalid
                // because the desired part is more specific than entered value and can not be validated fully
                $isValid = false;
                break;
            }

            if (!isset($partsEnteredValue[$i]) && !isset($partsDesired[$i])) {
                // Normally end of validation
                break;
            }

            if (isset($partsEnteredValue[$i]) && !isset($partsDesired[$i])) {
                // Normally end of validation
                if ($isValid) {
                    $isValid = $this->helper->getPostcodeExcessiveValid();
                }
                break;
            }

            switch ($operator) {
                case '<=':
                    $isValid = $partsEnteredValue[$i] <= $partsDesired[$i];
                    break;
                case '>=':
                    $isValid = $partsEnteredValue[$i] >= $partsDesired[$i];
                    break;
                default:
                    $isValid = false;
            }

            if (!$isValid) {
                break;
            }
        }

        return $isValid;
    }

    /**
     * Explode string by digits and letters part
     *
     * @param string $string
     *
     * @return array
     */
    private function explodeStringByAlphaDigits($string)
    {
        return $this->helper->explodeStringByAlphaDigits($string);
    }

    /**
     * @return array|string
     */
    public function getValueName()
    {
        $value = $this->getValue();
        if ($value === null || '' === $value) {
            return '...';
        }

        $options  = $this->getValueSelectOptions();
        $valueArr = [];

        // If there are no options we return the value as it is.
        if (empty($options)) {
            return $value;
        }

        foreach ($options as $option) {
            if (is_array($value) && is_array($option['value'])) {
                $valueArr = $this->processValueNameAsArray($valueArr, $value, $option);
            } elseif (is_array($value) && in_array($option['value'], $value)) {
                $valueArr[] = $option['label'];
            } elseif (isset($option['value'])) {
                $stringValue = $this->processValueNameAsString($option, $value);
                if ($stringValue) {
                    return $stringValue;
                }
            }
        }
        if (!empty($valueArr)) {
            $value = implode(', ', $valueArr);
        }

        return $value;
    }

    /**
     * Process option value as string
     *
     * @used id the getValueName() method ONLY
     *
     * @param array $valueArr
     * @param mixed $value
     * @param array $option
     * @return array
     */
    protected function processValueNameAsArray($valueArr, $value, $option)
    {
        foreach ($option['value'] as $subOption) {
            if (in_array($subOption['value'], $value)) {
                $valueArr[] = $subOption['label'];
            }
        }

        return $valueArr;
    }

    /**
     * Process option value as array
     *
     * @used id the getValueName() method ONLY
     *
     * @param array $option
     * @param mixed $value
     * @return null
     */
    protected function processValueNameAsString($option, $value)
    {
        if (is_array($option['value'])) {
            foreach ($option['value'] as $optionValue) {
                if ($optionValue['value'] == $value) {
                    return $optionValue['label'];
                }
            }
        }

        if ($option['value'] == $value) {
            return $option['label'];
        }

        return null;
    }

    /**
     * Parse UK postcode from the address and adds it to the addres (make available to validate it by specific part)
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return \Magento\Quote\Model\Quote\Address
     */
    protected function addUKPostCodeParts(\Magento\Quote\Model\Quote\Address $address)
    {
        if (!in_array($this->getAttribute(), static::UK_POST_CODE_ATTRIBUTES)) {
            return $address;
        }

        if (!$address->hasData($this->getAttribute())) {
            $ukZipParsedParts = $this->parseUkPostCode($address->getPostcode());
            $address->addData($ukZipParsedParts);
        }

        return $address;
    }

    /**
     * Parse UK postcode
     * Returns it by parts:
     *  'area'
     *  'district'
     *  'sector'
     *  'outcode'
     *  'incode'
     *  'formatted'
     *
     * @param string $postcode
     * @return array
     */
    protected function parseUkPostCode($postcode)
    {
        return $this->helper->parseUkPostCode($postcode);
    }
}

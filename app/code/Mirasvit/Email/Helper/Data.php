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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Helper;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * @codingStandardsIgnoreFile
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Variable\Model\VariableFactory
     */
    protected $variableFactory;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param TimezoneInterface                       $timezone
     * @param \Magento\Variable\Model\VariableFactory $variableFactory
     * @param \Magento\Framework\App\Helper\Context   $context
     */
    public function __construct(
        TimezoneInterface $timezone,
        \Magento\Variable\Model\VariableFactory $variableFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->timezone = $timezone;
        $this->variableFactory = $variableFactory;
        $this->context = $context;
        parent::__construct($context);
    }

    /**
     * @param string $body
     * @param \Mirasvit\Email\Model\Queue $queue
     * @return string
     */
    public function prepareQueueContent($body, $queue)
    {
        $trigger = $queue->getTrigger();

        if ($trigger->getGaSource() && $trigger->getGaMedium() && $trigger->getGaName()) {
            $ga = [];
            $ga[] = 'utm_source=' . rawurlencode($trigger->getGaSource());
            $ga[] = 'utm_medium=' . rawurlencode($trigger->getGaMedium());
            $ga[] = 'utm_campaign=' . rawurlencode($trigger->getGaName());
            if ($trigger->getGaTerm() != '') {
                $ga[] = 'utm_term=' . rawurlencode($trigger->getGaTerm());
            }
            if ($trigger->getGaContent() != '') {
                $ga[] = 'utm_content=' . rawurlencode($trigger->getGaContent());
            }

            $body = $this->addParamsToLinks($body, $ga);
        }

        return $body;
    }

    public function addParamsToLinks($text, $params)
    {
        if (is_array($params)) {
            $params = implode('&', $params);
        }

        $matches = [];
        if (preg_match_all('/<a\s[^>]*href=([\"\']??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/siU', $text, $matches)) {
            foreach ($matches[2] as $key => $link) {
                $components = parse_url($link);
                $newLink = false;
                if (isset($components['path']) && isset($components['host'])) {
                    if (isset($components['query'])) {
                        $newLink = $link . '&' . $params;
                    } else {
                        $newLink = $link . '?' . $params;
                    }
                }

                if (isset($components['fragment'])) {
                    $newLink = str_replace('#' . $components['fragment'], '', $newLink) . '#' . $components['fragment'];
                }

                if ($newLink) {
                    $from = $matches[0][$key];
                    $to = str_replace('href="' . $link . '"', 'href="' . $newLink . '"', $from);

                    $text = str_replace($from, $to, $text);
                }
            }
        }

        return $text;
    }

    /**
     * Convert Datetime from config timezone to default
     *
     * @param string $datetime which we want to convert
     *
     * @return string
     */
    public function convertTz($datetime = "")
    {
        $fromTz = $this->timezone->getConfigTimezone();
        $toTz = $this->timezone->getDefaultTimezone();

        $date = new \DateTime($datetime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('Y-m-d H:i:s');

        return $dateTime;
    }

    /**
     * Convert rule conditions to array compatible for the loadPost method.
     *
     * @param array       $condition
     * @param null|string $key
     *
     * @return array
     */
    public function convertConditionsToPost(array $condition, $key = null)
    {
        $resultCondition = [];
        if ($key === null) {
            $key = 1;
        }

        $resultCondition[$key] = $condition;
        unset($resultCondition[$key]['conditions']);

        if (isset($condition['conditions'])) {
            $key .= '--'; // append double dash character
            foreach ($condition['conditions'] as $idx => $option) {
                $condKey = $key . ($idx + 1); // append condition index number
                $result = $this->convertConditionsToPost($option, $condKey);
                unset($result[$condKey]['conditions']);
                $resultCondition = $resultCondition + $result; // use union operator to preserve array keys
            }
        }

        return $resultCondition;
    }
}

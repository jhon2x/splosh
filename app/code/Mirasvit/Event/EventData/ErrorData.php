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



namespace Mirasvit\Event\EventData;

use Magento\Framework\DataObject;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\ErrorCondition;
use Mirasvit\Event\Ui\Event\Source\ErrorLevel;

class ErrorData extends DataObject implements EventDataInterface
{
    const IDENTIFIER = 'error';

    /**
     * @var ErrorLevel
     */
    private $errorLevelSource;

    public function __construct(
        ErrorLevel $errorLevelSource,
        array $data = []
    ) {
        parent::__construct($data);

        $this->errorLevelSource = $errorLevelSource;
    }

    public function getIdentifier()
    {
        return 'error';
    }

    public function getLabel()
    {
        return __('Error');
    }

    public function getConditionClass()
    {
        return ErrorCondition::class;
    }

    public function getAttributes()
    {
        return [
            'level'     => [
                'label'   => __('Level'),
                'type'    => self::ATTRIBUTE_TYPE_ENUM,
                'options' => $this->errorLevelSource->getOptions(),
            ],
            'message'   => [
                'label' => __('Message'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'backtrace' => [
                'label' => __('Backtrace'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'request_uri' => [
                'label' => __('Request URI'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
        ];
    }

    public function getLevel()
    {
        return $this->getData('level');
    }

    public function getLevelLabel()
    {
        return $this->errorLevelSource->getOptions()[$this->getLevel()];
    }

    public function getMessage()
    {
        return $this->getData('message');
    }

    public function getBacktrace()
    {
        return $this->getData('backtrace');
    }

    public function getRequestUri()
    {
        return $this->getData('request_uri');
    }
}
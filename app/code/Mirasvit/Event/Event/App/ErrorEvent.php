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



namespace Mirasvit\Event\Event\App;

use Mirasvit\Event\Event\ObservableEvent;
use Mirasvit\Event\EventData\ErrorData;

class ErrorEvent extends ObservableEvent
{
    const IDENTIFIER = 'app_error';

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            self::IDENTIFIER => __('App / Error'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(ErrorData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        $params = $this->expand($params);

        /** @var ErrorData $error */
        $error = $params[ErrorData::IDENTIFIER];

        return __('%1: %2', $error->getLevelLabel(), $error->getMessage());
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        return [
            ErrorData::IDENTIFIER => $this->context->create(ErrorData::class, $params),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAddRecord($subject, $level, $message, array $context = [])
    {
        $params = [
            'level'                  => $level,
            'message'                => $message,
            'context'                => $context,
            'backtrace'              => \Magento\Framework\Debug::backtrace(true),
            self::PARAM_EXPIRE_AFTER => 0,
        ];

        $this->context->eventRepository->register(
            self::IDENTIFIER,
            [$params['level'], $params['message']],
            $params
        );

        return [$level, $message, $context];
    }

    /**
     * @param \Magento\Framework\App\Http $subject
     * @param \Magento\Framework\App\Bootstrap $bootstrap
     * @param \Exception $exception
     * @return array
     */
    public function beforeCatchException($subject, $bootstrap, $exception)
    {
        $params = [
            'level'                  => \Monolog\Logger::CRITICAL,
            'message'                => 'Exception: ' . $exception->getMessage(),
            'context'                => $exception->getCode(),
            'backtrace'              => $exception->getTraceAsString(),
            self::PARAM_EXPIRE_AFTER => 0,
        ];

        $this->context->eventRepository->register(
            self::IDENTIFIER,
            [$params['level'], $params['message']],
            $params
        );

        return [$bootstrap, $exception];
    }

    /**
     * @param \Magento\Framework\Webapi\ErrorProcessor $subject
     * @return array
     */
    public function beforeApiShutdownFunction($subject)
    {
        $fatalErrorFlag = E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR;
        $error = error_get_last();
        if ($error && $error['type'] & $fatalErrorFlag) {
            $errorMessage = "Fatal Error: '{$error['message']}' in '{$error['file']}' on line {$error['line']}";

            $params = [
                'level'                  => \Monolog\Logger::CRITICAL,
                'message'                => 'API Exception: ' . $errorMessage,
                'context'                => [],
                'backtrace'              => \Magento\Framework\Debug::backtrace(true),
                self::PARAM_EXPIRE_AFTER => 0,
            ];

            $this->context->eventRepository->register(
                self::IDENTIFIER,
                [$params['level'], $params['message']],
                $params
            );
        }

        return [];
    }
}
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
 * @package   mirasvit/module-message-queue
 * @version   1.0.9
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Mq\Service;

use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\ServiceOutputProcessor;

class EnvelopeEncoderService
{
    /**
     * @var ServiceInputProcessor
     */
    private $inputProcessor;

    /**
     * @var ServiceOutputProcessor
     */
    private $outputProcessor;

    public function __construct(
        ServiceInputProcessor $inputProcessor,
        ServiceOutputProcessor $outputProcessor
    ) {
        $this->inputProcessor = $inputProcessor;
        $this->outputProcessor = $outputProcessor;
    }

    /**
     * @param object|array $data
     * @param string $schema
     * @return string
     */
    public function encode($data, $schema = '')
    {
        return \Zend_Json::encode($data);
    }

    /**
     * @param string $data
     * @return object
     */
    public function decode($data)
    {
        $data = \Zend_Json::decode($data);

        return $data;
    }
}
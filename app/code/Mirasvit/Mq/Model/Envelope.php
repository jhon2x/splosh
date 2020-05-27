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



namespace Mirasvit\Mq\Model;

use Mirasvit\Mq\Api\Data\EnvelopeInterface;

class Envelope implements EnvelopeInterface
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var string
     */
    private $queueName;

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function setBody($value)
    {
        $this->body = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * {@inheritdoc}
     */
    public function setReference($value)
    {
        $this->reference = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName()
    {
        return $this->queueName;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueueName($value)
    {
        $this->queueName = $value;

        return $this;
    }
}
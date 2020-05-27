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



namespace Mirasvit\Mq\Repository;

use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Mq\Api\ConsumerInterfaceFactory;
use Mirasvit\Mq\Api\ConsumerInterface;
use Mirasvit\Mq\Api\Repository\ConsumerRepositoryInterface;

class ConsumerRepository implements ConsumerRepositoryInterface
{
    /**
     * @var ConsumerInterfaceFactory
     */
    private $consumerFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $consumers;

    public function __construct(
        ConsumerInterfaceFactory $consumerFactory,
        ObjectManagerInterface $objectManager,
        array $consumers = []
    ) {
        $this->consumerFactory = $consumerFactory;
        $this->objectManager = $objectManager;
        $this->consumers = $consumers;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        foreach ($this->consumers as $identifier => $data) {
            if ($identifier == $name) {
                list($class, $method) = explode('::', $data['callback']);
                $callback = [
                    $this->objectManager->create($class),
                    $method,
                ];

                return $this->consumerFactory->create([
                    'queueName' => $data['queue'],
                    'callback'  => $callback,
                ]);
            }
        }

        throw new \Exception(__("Consumer %1 wasn't found", $name));
    }

    /**
     * {@inheritdoc}
     */
    public function getByQueueName($queueName)
    {
        $consumers = [];
        foreach ($this->consumers as $identifier => $data) {
            $consumer = $this->get($identifier);
            if ($consumer->getQueueName() == $queueName) {
                $consumers[] = $consumer;
            }
        }

        return $consumers;
    }
}
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
use Mirasvit\Mq\Api\QueueInterface;

class QueueRepository
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $queues;

    /**
     * @var QueueInterface
     */
    private $instance;

    public function __construct(
        ObjectManagerInterface $objectManager,
        array $queues = []
    ) {
        $this->objectManager = $objectManager;
        $this->queues = $queues;
    }

    /**
     * @return QueueInterface
     */
    public function getProvider()
    {
        if (!$this->instance) {
            foreach ($this->queues as $class) {
                /** @var QueueInterface $instance */
                $instance = $this->objectManager->create($class);
                if ($instance->isAvailable()) {
                    $this->instance = $instance;
                    break;
                }
            }
        }

        return $this->instance;
    }
}
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

use Mirasvit\Mq\Api\Data\EnvelopeInterfaceFactory;
use Mirasvit\Mq\Api\PublisherInterface;
use Mirasvit\Mq\Repository\QueueRepository;
use Mirasvit\Mq\Service\EnvelopeEncoderService;


class Publisher implements PublisherInterface
{
    /**
     * @var EnvelopeInterfaceFactory
     */
    private $envelopeFactory;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var EnvelopeEncoderService
     */
    private $encoderService;

    public function __construct(
        EnvelopeInterfaceFactory $envelopeFactory,
        QueueRepository $queueRepository,
        EnvelopeEncoderService $encoderService
    ) {
        $this->envelopeFactory = $envelopeFactory;
        $this->queueRepository = $queueRepository;
        $this->encoderService = $encoderService;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($queueName, $data)
    {
        $envelope = $this->envelopeFactory->create()
            ->setQueueName($queueName)
            ->setBody($this->encoderService->encode($data));

        $this->queueRepository->getProvider()
            ->enqueue($envelope);

        return $this;
    }
}
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



namespace Mirasvit\Event\Event;

use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;
use Mirasvit\Event\Service\FlagService;
use Mirasvit\Event\Service\TimeService;
use Mirasvit\Event\Service\ValidatorService;

class Context
{
    /**
     * @var EventRepositoryInterface
     */
    public $eventRepository;

    /**
     * @var ValidatorService
     */
    public $validatorService;

    /**
     * @var TimeService
     */
    public $timeService;

    /**
     * @var FlagService
     */
    public $flagService;

    /**
     * @var ObjectManagerInterface
     */
    public $objectManager;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        ValidatorService $validatorService,
        TimeService $timeService,
        FlagService $flagService,
        ObjectManagerInterface $objectManager
    ) {
        $this->eventRepository = $eventRepository;
        $this->validatorService = $validatorService;
        $this->timeService = $timeService;
        $this->flagService = $flagService;
        $this->objectManager = $objectManager;
    }

    public function get($class)
    {
        return $this->objectManager->get($class);
    }

    public function create($class, $data = [])
    {
        return $this->objectManager->create($class, ['data' => $data]);
    }
}
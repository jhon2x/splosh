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


/**
 * Created by PhpStorm.
 * User: vav
 * Date: 20.03.17
 * Time: 18:07
 */

namespace Mirasvit\Email\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

class MassDelete extends Action
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * MassValidate constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     * @param Action\Context           $context
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->eventRepository = $eventRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $eventIds = $this->getRequest()->getParam('event_id');

        if (!is_array($eventIds)) {
            $this->messageManager->addErrorMessage(__('Please select event(s)'));
        } else {
            try {
                foreach ($eventIds as $eventId) {
                    $event = $this->eventRepository->get($eventId);
                    if ($event) {
                        $this->eventRepository->delete($event);
                    }
                }

                $this->messageManager->addSuccessMessage(
                    __('Total of %1 record(s) were deleted', count($eventIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}

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



namespace Mirasvit\Event\Controller\Adminhtml\Event;


use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

class MassDelete extends Action
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;
    /**
     * @var Filter
     */
    private $filter;

    public function __construct(
        Filter $filter,
        EventRepositoryInterface $eventRepository,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->eventRepository = $eventRepository;
        $this->filter = $filter;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->eventRepository->getCollection());

        if (!$collection->getSize()) {
            $this->messageManager->addErrorMessage(__('Please select event(s)'));
        } else {
            try {
                foreach ($collection as $event) {
                    $this->eventRepository->delete($event);
                }

                $this->messageManager->addSuccessMessage(
                    __('Total of %1 record(s) were deleted', $collection->getSize())
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setRefererOrBaseUrl();
    }
}

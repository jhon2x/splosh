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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Controller\Adminhtml\Synonym;

use Mirasvit\Search\Api\Data\SynonymInterface;
use Mirasvit\Search\Controller\Adminhtml\Synonym;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Mirasvit\Search\Api\Repository\SynonymRepositoryInterface;
use Mirasvit\Search\Api\Service\SynonymServiceInterface;

class Delete extends Synonym
{
    /**
     * @var Filter
     */
    private $filter;

    public function __construct(
        Filter $filter,
        SynonymRepositoryInterface $synonymRepository,
        SynonymServiceInterface $synonymService,
        Context $context
    ) {
        $this->filter = $filter;
        parent::__construct($synonymRepository, $synonymService, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {

        $ids = [];

        if ($this->getRequest()->getParam(SynonymInterface::ID)) {
            $ids = [$this->getRequest()->getParam(SynonymInterface::ID)];
        }

        if ($this->getRequest()->getParam(Filter::SELECTED_PARAM)
            || $this->getRequest()->getParam(Filter::EXCLUDED_PARAM)
        ) {
            $ids = $this->filter->getCollection($this->synonymRepository->getCollection())->getAllIds();
        }

        if ($ids) {
            foreach ($ids as $id) {
                try {
                    $page = $this->synonymRepository->get($id);
                    $this->synonymRepository->delete($page);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }

            $this->messageManager->addSuccessMessage(
                __('%1 item(s) was removed', count($ids))
            );
        } else {
            $this->messageManager->addErrorMessage(__('Please select item(s)'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}

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

use Mirasvit\Search\Controller\Adminhtml\Synonym;

class DoImport extends Synonym
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $result = [
                'synonyms' => 0,
                'errors'   => 0,
            ];

            $generator = $this->synonymService->import($data['dictionary'], $data['store_id']);
            foreach ($generator as $result) {
            }

            if ($result['synonyms'] > 0) {
                $this->messageManager->addSuccessMessage(__('Imported %1 synonym(s).', $result['synonyms']));
            }

            if ($result['errors']) {
                $this->messageManager->addWarningMessage(__('%1 errors.', $result['errors']));
            }
        } else {
            $this->messageManager->addErrorMessage('No data to import.');
        }

        return $resultRedirect->setPath('*/*/');
    }
}

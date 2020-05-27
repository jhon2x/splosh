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



namespace Mirasvit\Email\Controller\Action;

use Mirasvit\Email\Controller\Action;

class Resume extends Action
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($hash = $this->getRequest()->getParam('hash')) {
            $this->frontendHelper->loginCustomerByQueueHash($hash);
        }

        if ($to = $this->getRequest()->getParam('to')) {
            if (base64_decode($to)) {
                $to = base64_decode($to);
            }

            $this->getResponse()->setRedirect($this->_getUrl($to));
        } else {
            $this->getResponse()->setRedirect($this->_getUrl('/', true));
        }
    }
}

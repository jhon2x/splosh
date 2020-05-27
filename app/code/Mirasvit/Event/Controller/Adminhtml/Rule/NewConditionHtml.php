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



namespace Mirasvit\Event\Controller\Adminhtml\Rule;

use Magento\Rule\Model\Condition\AbstractCondition;

class NewConditionHtml extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));

        $attribute = false;

        $class = $typeArr[0];

        if (count($typeArr) == 2) {
            $attribute = $typeArr[1];
        }

        //$model = $this->_objectManager->create($class, ['data' => $this->getRequest()->getParams()]);
        $model = $this->_objectManager->create($class, ['data' => [
            'eventIdentifier' => $this->getRequest()->getParam('eventIdentifier')
        ]]);

        $model->setId($id)
            ->setType($class)
            ->setRule($this->_objectManager->create('Mirasvit\Event\Model\Rule'))
            ->setPrefix('conditions')
            ->setFormName($this->getRequest()->getParam('form_name', 'rule_edit_form'));

        if ($attribute) {
            $model->setAttribute($attribute);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    protected function _isAllowed()
    {
        return true;
    }
}

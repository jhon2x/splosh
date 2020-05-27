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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailDesigner\Ui\Theme\Form\Control;


use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Mirasvit\EmailDesigner\Api\Data\ThemeInterface;
use Mirasvit\EmailDesigner\Ui\Component\Control\GenericButton;
use Mirasvit\EmailDesigner\Controller\RegistryConstants;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getButtonData()
    {
        $data = [];
        /** @var ThemeInterface $theme */
        $theme = $this->registry(RegistryConstants::CURRENT_MODEL);

        if ($theme && $theme->getId() !== 1) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\''
                    . __('Are you sure you want to delete this Theme?')
                    . '\', \'' . $this->getDeleteUrl($theme->getId()) . '\')',
                'sort_order' => 20,
            ];
        }

        return $data;
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public function getDeleteUrl($id)
    {
        return $this->getUrl('*/*/delete', [ThemeInterface::ID => $id]);
    }
}

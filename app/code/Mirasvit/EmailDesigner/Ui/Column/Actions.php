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



namespace Mirasvit\EmailDesigner\Ui\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Actions extends AbstractColumn
{
    /** Url path */
    const URL_PATH_EDIT   = 'url_path_edit';
    const URL_PATH_DELETE = 'url_path_delete';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareItem(array $item)
    {
        $id = $this->getData('config/indexField');
        $urlPathEdit = $this->getData(self::URL_PATH_EDIT);
        $urlPathDelete = $this->getData(self::URL_PATH_DELETE);

        return [
            'edit'   => [
                'href'  => $this->urlBuilder->getUrl($urlPathEdit, [$id => $item[$id]]),
                'label' => __('Edit'),
            ],
            'delete' => [
                'href'    => $this->urlBuilder->getUrl($urlPathDelete, [$id => $item[$id]]),
                'label'   => __('Delete'),
                'confirm' => [
                    'title' => __('Delete item?'),
                ],
            ],
        ];
    }
}

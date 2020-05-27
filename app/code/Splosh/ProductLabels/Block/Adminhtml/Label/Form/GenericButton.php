<?php

namespace Splosh\ProductLabels\Block\Adminhtml\Label\Form;

use Magento\Backend\Block\Widget\Context;
use Splosh\ProductLabels\Model\LabelFactory;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Splosh\ProductLabels\Model\Label
     */
    protected $labelModel;

    /**
     * GenericButton constructor.
     * @param Context $context
     * @param LabelFactory $labelFactory
     */
    public function __construct(
        Context $context,
        LabelFactory $labelFactory
    ) {
        $this->labelModel = $labelFactory->create();
        $this->context = $context;
    }

    /**
     * @return mixed
     */
    public function getLabelId()
    {
        return $this->labelModel->load(
            $this->context->getRequest()->getParam('label_id')
        )->getId();
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}

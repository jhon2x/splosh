<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_BannersLite
 */


namespace Amasty\BannersLite\Ui\Component\Form;

use Magento\Ui\Component\Form\Field;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Module\Manager;
use Magento\Framework\UrlInterface;

class BannerLabel extends Field
{
    const MODULE_NAME = 'Amasty_Label';

    const LABEL_GUIDE_URL = 'https://amasty.com/product-labels-for-magento-2.html?utm_source=extension&utm_medium=link&utm_campaign=sp-plabels-m2';

    const LABEL_URL = 'amasty_label/labels';

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Manager $manager,
        $components,
        array $data = []
    ) {
        $this->manager = $manager;
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepare()
    {
        $config = $this->getData('config');

        if ($this->manager->isEnabled(self::MODULE_NAME)) {
            $url = $this->urlBuilder->getUrl(self::LABEL_URL);
            $config['additionalInfo'] = 'Label will be rendered at the upper left corner of on the product photo.'
                . ' For more options to customize your label message, use our Product Label extension which can be '
                . "<a href= ". $url ." target='_blank'>configured here</a>";
        } else {
            $config['additionalInfo'] = 'Label will be rendered at the upper left corner of the product photo. '
                . 'For more options to customize your label messages, consider using our Product Label extension. '
                . "<a href= ". self::LABEL_GUIDE_URL ." target='_blank'>Click here for more details</a>";
        }

        $this->setData('config', $config);

        parent::prepare();
    }
}

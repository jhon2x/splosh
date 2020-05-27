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



namespace Mirasvit\EmailDesigner\Model\Config\Source;


use Magento\Framework\Option\ArrayInterface;
use Mirasvit\EmailDesigner\Api\Repository\TemplateRepositoryInterface;
use Mirasvit\EmailDesigner\Model\Template as EmailTemplate;
use Mirasvit\EmailDesigner\Model\Email\Template as MagentoTemplate;
use \Magento\Email\Model\ResourceModel\Template\CollectionFactory;

class Template implements ArrayInterface
{
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;
    /**
     * @var CollectionFactory
     */
    private $emailCollectionFactory;

    public function __construct(
        CollectionFactory $emailCollectionFactory,
        TemplateRepositoryInterface $templateRepository
    )
    {
        $this->templateRepository= $templateRepository;
        $this->emailCollectionFactory = $emailCollectionFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        $magentoTemplates = $this->emailCollectionFactory->create()->toOptionArray();
        $templates = $this->templateRepository->getCollection()->toOptionArray();

        foreach ($templates as $idx => $template) {
            $templates[$idx]['value'] = EmailTemplate::TYPE.':'.$template['value'];
        }

        foreach ($magentoTemplates as $idx => $template) {
            $magentoTemplates[$idx]['value'] = MagentoTemplate::TYPE.':'.$template['value'];
        }

        $options = [
            EmailTemplate::TYPE => [
                'label'    => __('Follow Up Email'),
                'value'    => EmailTemplate::TYPE,
                'optgroup' => $templates
            ],
            MagentoTemplate::TYPE => [
                'label'    => __('Magento'),
                'value'    => MagentoTemplate::TYPE,
                'optgroup' => $magentoTemplates
            ]
        ];

        return $options;
    }
}

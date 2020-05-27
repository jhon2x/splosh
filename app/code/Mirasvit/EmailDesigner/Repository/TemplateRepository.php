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



namespace Mirasvit\EmailDesigner\Repository;


use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;
use Mirasvit\EmailDesigner\Api\Data\TemplateInterfaceFactory;
use Mirasvit\EmailDesigner\Api\Repository\TemplateRepositoryInterface;
use Mirasvit\EmailDesigner\Model\ResourceModel\Template\CollectionFactory;
use Mirasvit\EmailDesigner\Model\Email\TemplateFactory as EmailTemplateFactory;
use Mirasvit\EmailDesigner\Model\Email\Template as MagentoTemplate;
use Mirasvit\EmailDesigner\Model\Template as EmailTemplate;
use Mirasvit\Email\Helper\Serializer;

class TemplateRepository implements TemplateRepositoryInterface
{
    /**
     * @var TemplateInterface[]
     */
    private $templateRegistry = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TemplateInterfaceFactory
     */
    private $modelFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var EmailTemplateFactory
     */
    private $emailTemplateFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        EmailTemplateFactory     $emailTemplateFactory,
        EntityManager            $entityManager,
        TemplateInterfaceFactory $modelFactory,
        CollectionFactory        $collectionFactory,
        Serializer               $serializer
    ) {
        $this->entityManager        = $entityManager;
        $this->modelFactory         = $modelFactory;
        $this->collectionFactory    = $collectionFactory;
        $this->emailTemplateFactory = $emailTemplateFactory;
        $this->serializer           = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->modelFactory->create();
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        if (isset($this->templateRegistry[$id])) {
            return $this->templateRegistry[$id];
        }

        $type = EmailTemplate::TYPE;
        if (!is_numeric($id) && strpos($id, ':') !== false) {
            list($type, $id) = explode(':', $id);
        }

        if ($type === MagentoTemplate::TYPE) {
            $template = $this->emailTemplateFactory->create()->load($id);
        } else {
            $template = $this->create();
            $template = $this->entityManager->load($template, $id);
        }

        if ($template->getId()) {
            $this->templateRegistry[$id] = $template;
        } else {
            return false;
        }

        return $template;
    }

    /**
     * {@inheritDoc}
     */
    public function save(TemplateInterface $template)
    {
        if ($template->hasData(TemplateInterface::TEMPLATE_AREAS)) {
            $template->setTemplateAreasSerialized($this->serializer->serialize($template->getData(TemplateInterface::TEMPLATE_AREAS)));
        }

        return $this->entityManager->save($template);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(TemplateInterface $template)
    {
        return $this->entityManager->delete($template);
    }
}

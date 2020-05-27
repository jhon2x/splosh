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



namespace Mirasvit\EmailDesigner\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;
use Mirasvit\Email\Helper\Serializer;

class Template extends AbstractDb
{
    /**
     * @var Serializer
     */
    protected $serializer;

     /**
     * Constructor.
     *
     * @param Serialize $serializer
     * @param Context   $context
    */
    public function __construct(
        Serializer $serializer,
        Context    $context
    ) {
        $this->serializer = $serializer;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(TemplateInterface::TABLE_NAME, TemplateInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->hasCreatedAt()) {
            $object->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
        }

        $object->setUpdatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        if ($object->hasData('template_areas')) {
            $object->setData('template_areas_serialized', $this->serializer->serialize($object->getData('template_areas')));
        }

        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $templateAreas = $this->serializer->unserialize($object->getData('template_areas_serialized'));

        if (is_array($templateAreas)) {
            $object->setData('template_areas', $templateAreas);
        } else {
            $object->setData('template_areas', []);
        }

        return $this;
    }
}

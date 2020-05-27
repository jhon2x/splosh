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



namespace Mirasvit\EmailDesigner\Api\Repository;

use Magento\Framework\Exception\NoSuchEntityException;
use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;

interface TemplateRepositoryInterface
{
    /**
     * @return TemplateInterface[]|\Mirasvit\EmailDesigner\Model\ResourceModel\Template\Collection
     */
    public function getCollection();

    /**
     * @return TemplateInterface
     */
    public function create();

    /**
     * @param int $id
     *
     * @return TemplateInterface|false
     */
    public function get($id);

    /**
     * @param TemplateInterface $model
     * @return TemplateInterface
     */
    public function save(TemplateInterface $model);

    /**
     * @param TemplateInterface $model
     * @return bool
     */
    public function delete(TemplateInterface $model);
}

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



namespace Mirasvit\Email\Api\Repository\Trigger;

use Mirasvit\Email\Api\Data\ChainInterface;

interface ChainRepositoryInterface
{
    /**
     * @return ChainInterface[]|\Mirasvit\Email\Model\ResourceModel\Trigger\Chain\Collection
     */
    public function getCollection();

    /**
     * @return ChainInterface
     */
    public function create();

    /**
     * @param int $id
     * @return ChainInterface|false
     */
    public function get($id);

    /**
     * @param ChainInterface $model
     * @return ChainInterface
     */
    public function save(ChainInterface $model);

    /**
     * @param ChainInterface $model
     * @return bool
     */
    public function delete(ChainInterface $model);
}

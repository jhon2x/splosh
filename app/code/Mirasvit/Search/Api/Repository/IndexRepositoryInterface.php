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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Api\Repository;

use Mirasvit\Search\Api\Data\Index\InstanceInterface;
use Mirasvit\Search\Api\Data\IndexInterface;

interface IndexRepositoryInterface
{
    /**
     * @return \Mirasvit\Search\Model\ResourceModel\Index\Collection | IndexInterface[]
     */
    public function getCollection();

    /**
     * @return IndexInterface
     */
    public function create();

    /**
     * @param int|string $id Index ID or Identifier
     * @return IndexInterface
     */
    public function get($id);

    /**
     * @param IndexInterface $index
     * @return IndexInterface
     */
    public function save(IndexInterface $index);

    /**
     * @param IndexInterface $index
     * @return $this
     */
    public function delete(IndexInterface $index);

    /**
     * @param IndexInterface|string $index
     * @return InstanceInterface
     */
    public function getInstance($index);

    /**
     * @return InstanceInterface[]
     */
    public function getList();
}

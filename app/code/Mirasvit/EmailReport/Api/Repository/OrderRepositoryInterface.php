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
 * @package   mirasvit/module-email-report
 * @version   2.0.8
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailReport\Api\Repository;

use Mirasvit\EmailReport\Api\Data\OrderInterface;

interface OrderRepositoryInterface
{
    /**
     * @return \Mirasvit\EmailReport\Model\ResourceModel\Order\Collection|OrderInterface[]
     */
    public function getCollection();

    /**
     * @param int $id
     * @return OrderInterface|false
     */
    public function get($id);

    /**
     * @return OrderInterface
     */
    public function create();

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function save(OrderInterface $order);

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function ensure(OrderInterface $order);

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function delete(OrderInterface $order);
}

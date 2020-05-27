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

use Mirasvit\EmailReport\Api\Data\ClickInterface;

interface ClickRepositoryInterface
{
    /**
     * @return \Mirasvit\EmailReport\Model\ResourceModel\Click\Collection|ClickInterface[]
     */
    public function getCollection();

    /**
     * @param int $id
     * @return ClickInterface|false
     */
    public function get($id);

    /**
     * @return ClickInterface
     */
    public function create();

    /**
     * @param ClickInterface $click
     * @return ClickInterface
     */
    public function save(ClickInterface $click);

    /**
     * @param ClickInterface $click
     * @return ClickInterface
     */
    public function ensure(ClickInterface $click);

    /**
     * @param ClickInterface $click
     * @return bool
     */
    public function delete(ClickInterface $click);
}

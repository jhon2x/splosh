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

use Mirasvit\EmailReport\Api\Data\ReviewInterface;

interface ReviewRepositoryInterface
{
    /**
     * @return \Mirasvit\EmailReport\Model\ResourceModel\Review\Collection|ReviewInterface[]
     */
    public function getCollection();

    /**
     * @param int $id
     * @return ReviewInterface|false
     */
    public function get($id);

    /**
     * @return ReviewInterface
     */
    public function create();

    /**
     * @param ReviewInterface $review
     * @return ReviewInterface
     */
    public function save(ReviewInterface $review);

    /**
     * @param ReviewInterface $review
     * @return ReviewInterface
     */
    public function ensure(ReviewInterface $review);

    /**
     * @param ReviewInterface $review
     * @return bool
     */
    public function delete(ReviewInterface $review);
}

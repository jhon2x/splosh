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

use Mirasvit\EmailReport\Api\Data\OpenInterface;

interface OpenRepositoryInterface
{
    /**
     * @return \Mirasvit\EmailReport\Model\ResourceModel\Open\Collection|OpenInterface[]
     */
    public function getCollection();

    /**
     * @return OpenInterface
     */
    public function create();

    /**
     * @param int $id
     * @return OpenInterface|false
     */
    public function get($id);

    /**
     * @param OpenInterface $open
     * @return OpenInterface
     */
    public function save(OpenInterface $open);

    /**
     * @param OpenInterface $open
     * @return OpenInterface
     */
    public function ensure(OpenInterface $open);

    /**
     * @param OpenInterface $open
     * @return bool true on success
     */
    public function delete(OpenInterface $open);
}

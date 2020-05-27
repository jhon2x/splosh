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
 * @package   mirasvit/module-search-landing
 * @version   1.0.7
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchLanding\Api\Repository;

use Mirasvit\SearchLanding\Api\Data\PageInterface;

interface PageRepositoryInterface
{
    /**
     * @return \Mirasvit\SearchLanding\Model\ResourceModel\Page\Collection | PageInterface[]
     */
    public function getCollection();

    /**
     * @return PageInterface
     */
    public function create();

    /**
     * @param int $id
     * @return PageInterface
     */
    public function get($id);

    /**
     * @param PageInterface $page
     * @return PageInterface
     */
    public function save(PageInterface $page);

    /**
     * @param PageInterface $page
     * @return $this
     */
    public function delete(PageInterface $page);
}
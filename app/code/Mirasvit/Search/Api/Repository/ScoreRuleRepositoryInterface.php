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

use Mirasvit\Search\Api\Data\ScoreRuleInterface;

interface ScoreRuleRepositoryInterface
{
    /**
     * @return \Mirasvit\Search\Model\ResourceModel\ScoreRule\Collection | ScoreRuleInterface[]
     */
    public function getCollection();

    /**
     * @return ScoreRuleInterface
     */
    public function create();

    /**
     * @param int $id
     * @return ScoreRuleInterface
     */
    public function get($id);

    /**
     * @param ScoreRuleInterface $model
     * @return $this
     */
    public function save(ScoreRuleInterface $model);

    /**
     * @param ScoreRuleInterface $model
     * @return $this
     */
    public function delete(ScoreRuleInterface $model);
}

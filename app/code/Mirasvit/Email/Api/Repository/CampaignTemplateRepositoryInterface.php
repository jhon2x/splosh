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



namespace Mirasvit\Email\Api\Repository;

use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Mirasvit\Email\Api\Data\CampaignInterface;

interface CampaignTemplateRepositoryInterface
{
    /**
     * Load campaigns from fixtures and convert them to Collection.
     *
     * @return CampaignInterface[]|Collection
     * @throws \Exception
     */
    public function getCollection();

    /**
     * Get campaign template by campaign ID.
     *
     * @param string $id - filename of a campaign template.
     *
     * @return \Magento\Framework\DataObject|CampaignInterface
     * @throws LocalizedException
     */
    public function get($id);

    /**
     * Create campaign using fixture associated with the given $templateId.
     *
     * @param string $templateId - id of a campaign template fixture
     *
     * @return \Magento\Framework\Model\AbstractModel|CampaignInterface
     * @throws LocalizedException
     */
    public function create($templateId);
}

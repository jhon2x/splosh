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



namespace Mirasvit\Email\Api\Service;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Email\Api\Repository\RepositoryInterface;

interface CloneServiceInterface
{
    /**
     * Duplicate a passed model and add a new data given in a $data param.
     *
     * @param AbstractModel       $model      - model to be duplicated
     * @param RepositoryInterface $repository - repository used to save the model
     * @param array               $unsetData  - data for reset on duplicated model
     * @param array               $data       - new data for duplicated model
     *
     * @return AbstractModel
     */
    public function duplicate(AbstractModel $model, $repository, array $unsetData = [], array $data = []);
}

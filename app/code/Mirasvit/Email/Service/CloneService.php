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



namespace Mirasvit\Email\Service;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Email\Api\Service\CloneServiceInterface;

class CloneService implements CloneServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function duplicate(AbstractModel $model, $repository, array $unsetData = [], array $data = [])
    {
        $modelClone = clone $model;
        $modelClone->unsetData($unsetData)
            ->addData($data);

        $repository->save($modelClone);

        return $modelClone;
    }
}

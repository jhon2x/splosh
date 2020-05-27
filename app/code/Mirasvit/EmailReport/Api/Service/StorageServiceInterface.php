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



namespace Mirasvit\EmailReport\Api\Service;

use Mirasvit\Email\Api\Data\QueueInterface;

interface StorageServiceInterface
{
    const QUEUE_PARAM_NAME = 'qid';

    /**
     * Save queue hash in customer's cookie.
     *
     * @param string $hash
     *
     * @return mixed
     */
    public function persistQueueHash($hash);

    /**
     * Retrieve queue ID from customer's cookie.
     *
     * @return int
     */
    public function retrieveQueueHash();

    /**
     * @param string $hash
     * @return QueueInterface|false
     */
    public function retrieveQueue($hash = false);
}

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



namespace Mirasvit\Email\Controller;

/**
 * Declaration of core registry keys used by Email module
 */
class RegistryConstants
{
    /**
     * Registry key where current trigger ID is stored.
     */
    const CURRENT_TRIGGER_ID = 'current_trigger_id';

    /**
     * Registry key where current campaign ID is stored.
     */
    const CURRENT_CAMPAIGN_ID = 'current_campaign_id';

    /**
     * Registry key where current model instance is stored.
     */
    const CURRENT_MODEL = 'current_model';

    /**
     * Registry key where current queue model instance is stored.
     */
    const CURRENT_QUEUE = 'current_email_queue';
}

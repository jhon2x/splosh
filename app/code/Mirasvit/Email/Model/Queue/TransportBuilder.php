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



namespace Mirasvit\Email\Model\Queue;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * Set message subject
     *
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->message->setSubject($subject);

        return $this;
    }

    /**
     * Set message body
     *
     * @param string $body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->message->setBody($body);

        if (method_exists($this->message, 'setBodyHtml')) {
            $this->message->setBodyHtml($body);
        }

        return $this;
    }

    /**
     * Set from
     *
     * @param string $email
     * @param string $name
     *
     * @return $this
     */
    public function setFrom($email, $name = null)
    {
        $this->message->setFrom($email, $name);

        return $this;
    }

    /**
     * Set message type
     *
     * @param string $type
     *
     * @return $this
     */
    public function setMessageType($type)
    {
        $this->message->setMessageType($type);

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function prepareMessage()
    {
        return $this;
    }
}

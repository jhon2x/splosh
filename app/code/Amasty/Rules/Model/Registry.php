<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model;

use Magento\Framework\DataObject;

/**
 * Data keeper.
 */
class Registry extends DataObject
{
    /**
     * @param string $key
     * @param mixed $value
     * @param bool $graceful
     *
     * @return Registry
     *
     * @throws \RuntimeException
     */
    public function register($key, $value, $graceful = false)
    {
        if (isset($this->_registry[$key])) {
            if ($graceful) {
                return $this;
            }
            throw new \RuntimeException('Key "' . $key . '" already exists');
        }

        return $this->setData($key, $value);
    }

    /**
     * @param string $key
     */
    public function unregister($key)
    {
        if (isset($this->_data[$key])) {
            unset($this->_data[$key]);
        }
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function registry($key)
    {
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }

        return null;
    }
}

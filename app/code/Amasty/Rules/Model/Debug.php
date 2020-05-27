<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Assist class for debug feature.
 */
class Debug
{
    const LOCAL_IP = '127.0.0.1';

    /**
     * @var ConfigModel
     */
    private $configModel;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var array
     */
    private $addressPath = [
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR'
    ];

    public function __construct(
        ConfigModel $configModel,
        RemoteAddress $remoteAddress,
        RequestInterface $request
    ) {
        $this->configModel = $configModel;
        $this->remoteAddress = $remoteAddress;
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function isDebugDisplayAllowed()
    {
        return $this->configModel->isDebugEnable() && $this->isCurrentIpAllow();
    }

    /**
     * @return bool
     */
    private function isCurrentIpAllow()
    {
        $userIp = $this->getCurrentIp();
        $allowedIps = $this->configModel->getAllowedIps();

        return strpos($allowedIps ?: '', $userIp) !== false;
    }

    /**
     * @return string
     */
    private function getCurrentIp()
    {
        foreach ($this->addressPath as $path) {
            $ip = $this->request->getServer($path);
            if ($ip) {
                if (strpos($ip, ',') !== false) {
                    $addresses = explode(',', $ip);
                    foreach ($addresses as $address) {
                        if (trim($address) != self::LOCAL_IP) {
                            return trim($address);
                        }
                    }
                } else {
                    if ($ip != self::LOCAL_IP) {
                        return $ip;
                    }
                }
            }
        }

        return $this->remoteAddress->getRemoteAddress();
    }
}

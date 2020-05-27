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
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Service;


use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Mirasvit\Event\Model\Config as EventConfig;
use Mirasvit\Event\Api\Service\GeoIpValidatorInterface;
use Mirasvit\Event\Model\Config\Source\CaptureStatus;
use GeoIp2\Database\Reader;

class GeoIpValidator implements GeoIpValidatorInterface
{
    /**
     * @var Reader|null
     */
    private $reader;
    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    public function __construct(RemoteAddress $remoteAddress, EventConfig $eventConfig)
    {
        $this->remoteAddress = $remoteAddress;

        if ($eventConfig->getCaptureStatus() === CaptureStatus::STATUS_OFF_EU) {
            $this->reader = new Reader($eventConfig->getGeoDbPath());
        }
    }

    /**
     * Call this method only when Reader object is properly instantiated.
     *
     * {@inheritdoc}
     */
    public function assertContinentNotEquals($continent, $targetIp = null)
    {
        if (!$targetIp) {
            $targetIp = $this->remoteAddress->getRemoteAddress();
        }

        $record = $this->reader->country($targetIp);

        return $record->continent->code !== $continent;
    }
}

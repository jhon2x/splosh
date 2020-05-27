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
 * @package   mirasvit/module-search-report
 * @version   1.0.5
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchReport\Service;

use Mirasvit\SearchReport\Api\Repository\LogRepositoryInterface;
use Mirasvit\SearchReport\Api\Service\LogServiceInterface;
use Magento\Customer\Model\SessionFactory;

class LogService implements LogServiceInterface
{
    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;

    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    public function __construct(
        LogRepositoryInterface $logRepository,
        SessionFactory $sessionFactory
    ) {
        $this->logRepository = $logRepository;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function logQuery($query, $results, $source, $misspellQuery, $fallbackQuery)
    {
        if (trim($query) == "") {
            return $this;
        }

        $log = $this->logRepository->create();

        $session = $this->sessionFactory->create();

        $log->setQuery($query)
            ->setMisspellQuery($misspellQuery)
            ->setFallbackQuery($fallbackQuery)
            ->setResults($results)
            ->setCustomerId($session->getCustomerId())
            ->setIp($this->getIp())
            ->setSession($session->getSessionId())
            ->setSource($source);

        return $this->logRepository->save($log);
    }

    /**
     * {@inheritdoc}
     */
    public function logClick($logId)
    {
        $log = $this->logRepository->get($logId);

        if ($log) {
            $log->setClicks($log->getClicks() + 1);
            return $this->logRepository->save($log);
        }

        return false;
    }

    /**
     * @return string
     */
    private function getIp()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

}
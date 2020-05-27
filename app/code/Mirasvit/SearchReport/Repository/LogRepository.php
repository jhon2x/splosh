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



namespace Mirasvit\SearchReport\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\SearchReport\Api\Data\LogInterface;
use Mirasvit\SearchReport\Api\Data\LogInterfaceFactory;
use Mirasvit\SearchReport\Api\Repository\LogRepositoryInterface;

class LogRepository implements LogRepositoryInterface
{
    /**
     * @var LogInterfaceFactory
     */
    private $logFactory;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        LogInterfaceFactory $logFactory,
        EntityManager $entityManager
    ) {
        $this->logFactory = $logFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->logFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $log = $this->logFactory->create();

        $this->entityManager->load($log, $id);

        if ($log->getId()) {
            return $log;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function save(LogInterface $log)
    {
        $log->setQuery(strtolower($log->getQuery()))
            ->setMisspellQuery(strtolower($log->getMisspellQuery()))
            ->setFallbackQuery(strtolower($log->getFallbackQuery()));

        if (!$log->getMisspellQuery()) {
            $log->setMisspellQuery(null);
        }
        if (!$log->getFallbackQuery()) {
            $log->setFallbackQuery(null);
        }

        return $this->entityManager->save($log);
    }
}
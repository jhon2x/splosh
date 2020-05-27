<?php

namespace Acidgreen\MagentoSales\Model\Order\Email;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;

class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerRegistry;

    public function __construct(
        \Magento\Sales\Model\Order\Email\Container\Template $templateContainer,
        \Magento\Sales\Model\Order\Email\Container\IdentityInterface $identityContainer,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry
    ) {
        $this->customerRegistry = $customerRegistry;
        parent::__construct($templateContainer, $identityContainer, $transportBuilder);
    }

    public function send()
    {
        $logger = new \Zend\Log\Logger;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/SPL-395-SenderBuilder.log');
        $logger->addWriter($writer);

        if (!($this->identityContainer instanceof \Magento\Sales\Model\Order\Email\Container\OrderIdentity)) {
            parent::send();
        }

        $this->configureEmailTemplate();

        $this->transportBuilder->addTo(
            $this->identityContainer->getCustomerEmail(),
            $this->identityContainer->getCustomerName()
        );

        if ($this->identityContainer instanceof \Magento\Sales\Model\Order\Email\Container\OrderIdentity) {
            // load customer model by email
            $customerEmail = $this->identityContainer->getCustomerEmail();

            try{
                $customer = $this->customerRegistry->retrieveByEmail($this->identityContainer->getCustomerEmail());
                // get salesperson and its object
                $exoStaffId = (int)$customer->getSalesperson();
                $salesperson = $this->getStaffByExoId($exoStaffId);

                // get email address of salesperson
                // if not empty add as cc thru $this->transportBuilder->addCc($salespersonEmail);
                if (!empty($salesperson) && !empty($salesperson->getEmail())) {
                    $this->transportBuilder->addCc($salesperson->getEmail());
                }

            } catch (\Exception $e) {
                $logger->debug('SPL-395 :: ERROR ENCOUNTERED :: '.$e->getMessage());
                $logger->debug('SPL-395 :: STACK TRACE:: '.$e->getTraceAsString().'<br>');
            }
        }

        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'bcc') {
            foreach ($copyTo as $email) {
                $this->transportBuilder->addBcc($email);
            }
        }

        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $logger->debug('SPL-395 :: ERROR ENCOUNTERED :: '.$e->getMessage());
            $logger->debug('SPL-395 :: STACK TRACE:: '.$e->getTraceAsString().'<br>');
        }
    }

    private function getStaffByExoId($exoStaffId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $currentWebsiteId = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getWebsite()->getId();
        $staffCollection = $objectManager->get('\Acidgreen\SploshExo\Model\Staff')->getCollection();
        $staffCollection
            ->addFieldToFilter('exo_staff_id', $exoStaffId)
            ->addFieldToFilter('website_id', $currentWebsiteId);

        if (count($staffCollection) > 0) {
            return $staffCollection->getFirstItem();
        }
        return null;
    }
}

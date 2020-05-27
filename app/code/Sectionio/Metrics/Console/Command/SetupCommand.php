<?php
/**
 * Copyright © 2017 Sectionio. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Sectionio\Metrics\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GreetingCommand
 */
class SetupCommand extends Command
{
    /**
     * Name argument
     */
    const USERNAME_ARGUMENT = 'username';

    /**
     * Password argument
     */
    const PASSWORD_ARGUMENT = 'password';

    /**
     * Account ID argument
     */
    const ACCOUNT_ID_ARGUMENT = 'account_id';

    /**
     * Application ID argument
     */
    const APPLICATION_ID_ARGUMENT = 'application_id';

    /** @var \Sectionio\Metrics\Model\SettingsFactory $settingsFactory */
    protected $settingsFactory;
    /** @var \Sectionio\Metrics\Helper\Data $helper */
    protected $helper;
    /** @var \Magento\Framework\Message\ManagerInterface $messageManager **/
    protected $messageManager;
    /** @var \Magento\Framework\App\State $state **/
    protected $state;

    /**
     * @param \Sectionio\Metrics\Model\SettingsFactory $settingsFactory
     * @param \Sectionio\Metrics\Helper\Data $helper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Sectionio\Metrics\Model\SettingsFactory $settingsFactory,
        \Sectionio\Metrics\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\State $state
    ) {
        parent::__construct();
        $this->settingsFactory = $settingsFactory;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sectionio:setup')
            ->setDescription('Setup the section.io module with your account details')
            ->setDefinition([
                new InputArgument(
                    self::USERNAME_ARGUMENT,
                    InputArgument::REQUIRED,
                    'section.io username'
                ),
                new InputArgument(
                    self::PASSWORD_ARGUMENT,
                    InputArgument::REQUIRED,
                    'section.io password'
                ),
                new InputArgument(
                    self::ACCOUNT_ID_ARGUMENT,
                    InputArgument::REQUIRED,
                    'section.io account ID'
                ),
                new InputArgument(
                    self::APPLICATION_ID_ARGUMENT,
                    InputArgument::REQUIRED,
                    'section.io application ID'
                ),
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument(self::USERNAME_ARGUMENT);
        $password = $input->getArgument(self::PASSWORD_ARGUMENT);
        $account_id = filter_var($input->getArgument(self::ACCOUNT_ID_ARGUMENT), FILTER_VALIDATE_INT);
        $application_id = filter_var($input->getArgument(self::APPLICATION_ID_ARGUMENT), FILTER_VALIDATE_INT);

        if ($account_id === false) {
            throw new \InvalidArgumentException('Argument ' . self::ACCOUNT_ID_ARGUMENT . ' must be your account id number.');
        }

        if ($application_id === false) {
            throw new \InvalidArgumentException('Argument ' . self::APPLICATION_ID_ARGUMENT . ' must be your application id number.');
        }

        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        // Attempt to set the AreaCode for the App State created by the ObjectManager
        // In Magento Commerce 2.2.3 the \Magento\Framework\App\State object returned by the ObjectManager is a
        // different instance to the one injected by DI
        $appStateOm = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\App\State::class);
        try {
            $appStateOm->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            //AreaCode is already set, move on
        }

        $settingsFactory = $this->settingsFactory->create()->getCollection()->getFirstItem();

        // Load existing model if available
        $general_id = $settingsFactory->getData('general_id');
        if ($general_id) {
            $settingsFactory->load($general_id);
        }

        // delete all existing accounts in case there was a different login used previously
        $this->helper->cleanSettings();

        //Update the username & password
        $settingsFactory->setData('user_name', $username);
        $this->helper->savePassword($settingsFactory, $password);
        $settingsFactory->save();

        //Refresh the account/application list
        $this->helper->refreshApplications($this->messageManager, $account_id, $application_id);
        $errors = $this->messageManager->getMessages()->getErrors();
        if ($errors && count($errors) > 0) {
            throw new \Exception($errors[0]->getText());
        }

        $this->helper->setDefaultAccount($account_id);
        $this->helper->setDefaultApplication($application_id);
    }
}

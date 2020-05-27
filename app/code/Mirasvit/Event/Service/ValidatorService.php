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

use Mirasvit\Event\Api\Service\ValidatorServiceInterface;
use Mirasvit\Event\Model\Rule\DataObjectFactory;
use Mirasvit\Event\Model\RuleFactory;

class ValidatorService implements ValidatorServiceInterface
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    public function __construct(
        RuleFactory $ruleFactory,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($conditions, $data)
    {
        $rule = $this->ruleFactory->create();
        $rule->loadPost($conditions);

        $dataObject = $this->dataObjectFactory->create();
        $dataObject->setData($data);

        $result = $rule->validate($dataObject);

        //        echo 'Result: ' . $result . PHP_EOL;

        return $result;
    }
}
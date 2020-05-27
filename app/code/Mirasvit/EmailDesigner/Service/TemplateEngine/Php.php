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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailDesigner\Service\TemplateEngine;


use Magento\Framework\DataObject;
use Mirasvit\EmailDesigner\Api\Service\TemplateEngineInterface;
use Mirasvit\EmailDesigner\Model\Config;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable\Pool as VariablePool;

class Php extends DataObject implements TemplateEngineInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var VariablePool
     */
    private $variablePool;

    public function __construct(
        Config       $config,
        VariablePool $variablePool,
        array        $data = []
    ) {
        $this->config       = $config;
        $this->variablePool = $variablePool;

        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, array $variables = [])
    {
        $this->variablePool->getContext()
            ->unsetData()
            ->setVariablePool($this->variablePool)
            ->addData($variables);

        $this->addData($variables);

        $tplPath = $this->config->getTmpPath() . '/' . time() . rand(1, 10000) . '.phtml';

        file_put_contents($tplPath, $template);

        $html = $this->getHtml($tplPath);

        if (file_exists($tplPath)) {
            unlink($tplPath);
        }

        return $html;
    }

    /**
     * Get area content.
     *
     * @param  string $area
     * @param  bool   $default
     *
     * @return string
     */
    private function area($area, $default = false)
    {
        if ($this->hasData('area_' . $area)) {
            $tplContent = $this->getData('area_' . $area);

            return $this->render($tplContent, $this->getData());
        }

        if ($this->variablePool->getContext()->getData('preview')) {
            if ($default) {
                return $default;
            }

            return true;
        }

        return '';
    }

    /**
     * Get html.
     *
     * @param string $tplPath
     * @return string
     */
    private function getHtml($tplPath)
    {
        ob_start();
        $handle = fopen($tplPath, "r");
        $html = fread($handle, filesize($tplPath));
        fclose($handle);
        ob_get_clean();

        return $html;
    }
    /**
     * Call method.
     *
     * @param  string $method
     * @param  array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        $result = $this->variablePool->resolve($method, $args);
        if ($result === false) {
            $result = parent::__call($method, $args);
        }

        return $result;
    }

    /**
     * Get data.
     *
     * @param  string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->variablePool->resolve($name);
    }
}

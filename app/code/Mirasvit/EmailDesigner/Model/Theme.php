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



namespace Mirasvit\EmailDesigner\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Mirasvit\EmailDesigner\Api\Data\ThemeInterface;
use Mirasvit\Core\Service\YamlService as YamlParser;

class Theme extends AbstractModel implements ThemeInterface
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Config $config,
        Context $context,
        Registry $registry
    ) {
        $this->config = $config;

        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Mirasvit\EmailDesigner\Model\ResourceModel\Theme::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * {@inheritDoc}
     */
    public function setTitle($title)
    {
        $this->setData(self::TITLE, $title);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION, $description);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplateText()
    {
        return $this->getData(self::TEMPLATE_TEXT);
    }

    /**
     * {@inheritDoc}
     */
    public function setTemplateText($text)
    {
        $this->setData(self::TEMPLATE_TEXT, $text);

        return $this;
    }

    /**
     * List of defined areas in template texts
     *
     * @return array
     */
    public function getAreas()
    {
        $areas = [];
        $matches = [];

        if (!preg_match_all(self::AREA_PATTERN_PHP, $this->getTemplateText(), $matches)) {
            preg_match_all(self::AREA_PATTERN_LIQUID, $this->getTemplateText(), $matches);
        }

        foreach ($matches[1] as $code) {
            $label = $code;
            $label = str_replace('_', ' ', $label);

            $areas[$code] = ucwords($label);
        }

        return $areas;
    }

    /**
     * Export theme
     *
     * @return string
     */
    public function export()
    {
        $path = $this->config->getThemePath() . '/' . $this->getTitle() . '.json';

        file_put_contents($path, $this->toJson());

        return $path;
    }

    /**
     * Import theme
     *
     * @param string $filePath
     * @return Theme
     */
    public function import($filePath)
    {
        $parser = new YamlParser();

        $data = $parser->parse(file_get_contents($filePath));

        $model = $this->getCollection()
            ->addFieldToFilter('title', $data['title'])
            ->getFirstItem();

        $model->addData($data)
            ->save();

        return $model;
    }
}

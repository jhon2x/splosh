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
use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;
use Mirasvit\EmailDesigner\Api\Data\ThemeInterface;
use Mirasvit\Core\Service\YamlService as YamlParser;
use Mirasvit\Email\Helper\Serializer;

class Template extends AbstractModel implements TemplateInterface
{
    const TYPE = 'emaildesigner';

    /**
     * @var ThemeFactory
     */
    protected $themeFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $areas;

    /**
     * @var Theme
     */
    protected $theme;

    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct(
        ThemeFactory $themeFactory,
        Config       $config,
        Context      $context,
        Registry     $registry,
        Serializer   $serializer
    ) {
        $this->themeFactory = $themeFactory;
        $this->config       = $config;
        $this->serializer   = $serializer;

        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Mirasvit\EmailDesigner\Model\ResourceModel\Template::class);
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
    public function getTemplateSubject()
    {
        return $this->getData(self::TEMPLATE_SUBJECT);
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplateText()
    {
        return $this->getTheme()->getTemplateText();
    }

    /**
     * {@inheritDoc}
     */
    public function setTemplateSubject($subject)
    {
        $this->setData(self::TEMPLATE_SUBJECT, $subject);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAreas()
    {
        if ($this->areas == null) {
            if ($this->getTheme()) {
                $this->areas = $this->getTheme()->getAreas();
            } else {
                $this->areas = ['content' => 'Content'];
            }
        }

        return $this->areas;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateAreas()
    {
        if (!$this->hasData(self::TEMPLATE_AREAS)) {
            $templateAreas = $this->serializer->unserialize($this->getTemplateAreasSerialized());
            if (is_array($templateAreas)) {
                $this->setData(self::TEMPLATE_AREAS, $templateAreas);
            } else {
                $this->setData(self::TEMPLATE_AREAS, []);
            }
        }

        return $this->getData(self::TEMPLATE_AREAS);
    }

    /**
     * {@inheritDoc}
     */
    public function setTemplateAreas(array $areas)
    {
        $this->setData(self::TEMPLATE_AREAS, $areas);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplateAreasSerialized()
    {
        return $this->getData(self::TEMPLATE_AREAS_SERIALIZED);
    }

    /**
     * {@inheritDoc}
     */
    public function setTemplateAreasSerialized($dataSerialized)
    {
        $this->setData(self::TEMPLATE_AREAS_SERIALIZED, $dataSerialized);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getThemeId()
    {
        return $this->getData(ThemeInterface::ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setThemeId($themeId)
    {
        $this->setData(ThemeInterface::ID, $themeId);

        return $this;
    }

    /**
     * Set theme
     *
     * @param ThemeInterface $theme
     * @return $this
     */
    public function setTheme(ThemeInterface $theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTheme()
    {
        if ($this->theme == null && $this->getThemeId()) {
            $this->theme = $this->themeFactory->create()
                ->load($this->getThemeId());
        }

        return $this->theme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAreaText($code)
    {
        if (isset($this->getTemplateAreas()[$code])) {
            return $this->getTemplateAreas()[$code];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setAreaText($code, $content)
    {
        $templateAreas = $this->getTemplateAreas();
        $templateAreas[$code] = $content;
        $this->setData(self::TEMPLATE_AREAS, $templateAreas);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAreaCodeByContent($content)
    {
        $code = null;
        foreach ($this->getTemplateAreas() as $areaCode => $areaContent) {
            if ($areaContent == $content) {
                $code = $areaCode;
                break;
            }
        }

        return $code;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isSystem()
    {
        return $this->hasData(TemplateInterface::SYSTEM_ID) && $this->getData(TemplateInterface::SYSTEM_ID) > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function setSystemId($id)
    {
        $this->setData(TemplateInterface::SYSTEM_ID, $id);

        return $this;
    }

    /**
     * Export template
     *
     * @return string
     */
    public function export()
    {
        $this->setData('theme', $this->getTheme()->getTitle());

        $path = $this->config->getTemplatePath() . '/' . $this->getTitle() . '.json';

        file_put_contents($path, $this->toJson());

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function import($filePath)
    {
        $data = YamlParser::parse(file_get_contents($filePath));

        // retrieve system ID from fixture filename
        $pathParts = explode('/', $filePath);
        $nameParts = explode('_', array_pop($pathParts));
        $systemId  = array_shift($nameParts);

        /** @var Template $model */
        $model = $this->getCollection()
            ->addFieldToFilter('title', $data['title'])
            ->getFirstItem();

        $model->addData($data);

        /** @var Theme $theme */
        $theme = $this->themeFactory->create()->getCollection()
            ->addFieldToFilter('title', $data['theme'])
            ->getFirstItem();

        $model->setThemeId($theme->getId());
        if (is_numeric($systemId)) {
            $model->setSystemId($systemId);
        }

        $model->save();

        return $model;
    }
}

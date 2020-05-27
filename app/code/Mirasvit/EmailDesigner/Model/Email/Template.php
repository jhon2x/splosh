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



namespace Mirasvit\EmailDesigner\Model\Email;

use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;
use Magento\Email\Model\Template as EmailTemplate;
use Mirasvit\EmailDesigner\Api\Data\ThemeInterface;
use Mirasvit\EmailDesigner\Model\Email\Context as TemplateContext;

class Template extends EmailTemplate implements TemplateInterface
{
    const TYPE          = 'email';
    const ADDED_AT      = 'added_at';
    const MODIFIED_AT   = 'modified_at';
    const TEMPLATE_CODE = 'template_code';
    /**
     * @var TemplateContext
     */
    private $templateContext;

    public function __construct(
        TemplateContext $templateContext,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Email\Model\TemplateFactory $templateFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\UrlInterface $urlModel,
        \Magento\Email\Model\Template\FilterFactory $filterFactory,
        array $data = []
    ) {
        $this->templateContext = $templateContext;

        parent::__construct(
            $context,
            $design,
            $registry,
            $appEmulation,
            $storeManager,
            $assetRepo,
            $filesystem,
            $scopeConfig,
            $emailConfig,
            $templateFactory,
            $filterManager,
            $urlModel,
            $filterFactory,
            $data
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return $this->getData(self::TEMPLATE_CODE);
    }

    /**
     * {@inheritDoc}
     */
    public function setTitle($title)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription($description)
    {
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
        return $this->getData(ThemeInterface::TEMPLATE_TEXT);
    }

    /**
     * {@inheritDoc}
     */
    public function setTemplateSubject($subject)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAreas()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateAreas()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function setTemplateAreas(array $areas)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplateAreasSerialized()
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function setTemplateAreasSerialized($dataSerialized)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getThemeId()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function setThemeId($themeId)
    {
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
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTheme()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAreaText($code)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setAreaText($code, $content)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAreaCodeByContent($content)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::MODIFIED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::ADDED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isSystem()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function setSystemId($id)
    {
        return $this;
    }

    /**
     * Export template
     *
     * @return string
     */
    public function export()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function import($filePath)
    {
        return $this;
    }

    protected function addEmailVariables($variables, $storeId)
    {
        $variables = parent::addEmailVariables($variables, $storeId);
        $variables = $this->templateContext->addEmailVariables($variables, $storeId);

        return $variables;
    }
}

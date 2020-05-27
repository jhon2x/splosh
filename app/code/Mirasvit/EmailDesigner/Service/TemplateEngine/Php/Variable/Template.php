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



namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\FileSystem as ViewFilesystem;
use Magento\Email\Model\TemplateFactory as EmailTemplateFactory;
use Magento\Store\Model\StoreManagerInterface;

class Template
{
    protected $filesystem;

    protected $viewFilesystem;

    protected $emailTemplate;

    protected $storeManager;

    public function __construct(
        Filesystem $filesystem,
        ViewFilesystem $viewFilesystem,
        EmailTemplateFactory $emailTemplate,
        StoreManagerInterface $storeManager
    ) {
        $this->filesystem = $filesystem;
        $this->viewFilesystem = $viewFilesystem;
        $this->emailTemplate = $emailTemplate;
        $this->storeManager = $storeManager;
    }


    public function getTemplate($templateFile)
    {
        try {
            list($module, $template) = explode('::', $templateFile);
            $tmpl = $this->emailTemplate->create();

            $tmpl->emulateDesign($this->storeManager->getStore()->getId());

            $designParams = $this->emailTemplate->create()->getDesignParams();
            $designParams['module'] = $module;

            $path = $this->viewFilesystem->getEmailTemplateFileName($template, $designParams, $designParams['module']);
            if (!$path) {
                throw new \Exception("Template wasn't found");
            }
            $rootDirectory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
            $templateText = $rootDirectory->readFile($rootDirectory->getRelativePath($path));

            return $templateText;

        } catch (\Exception $e) {
            return $e;
        }
    }
}

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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Config
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    /**
     * Base path
     *
     * @return string
     */
    public function getBasePath()
    {
        $dir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'email_designer';
        if (!file_exists($dir)) {
            mkdir($dir);
        }

        return $dir;
    }

    /**
     * Temporary path
     *
     * @return string
     */
    public function getTmpPath()
    {
        $dir = $this->getBasePath() . '/tmp';
        if (!file_exists($dir)) {
            mkdir($dir);
        }

        return $dir;
    }

    /**
     * Theme path
     *
     * @return string
     */
    public function getThemePath()
    {
        $dir = $this->getBasePath() . '/theme';
        if (!file_exists($dir)) {
            mkdir($dir);
        }

        return $dir;
    }

    /**
     * Template path
     *
     * @return string
     */
    public function getTemplatePath()
    {
        $dir = $this->getBasePath() . '/template';
        if (!file_exists($dir)) {
            mkdir($dir);
        }

        return $dir;
    }
}

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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Search\Api\Service\CloudServiceInterface;
use Mirasvit\Search\Model\Config;

class StopwordDictionary implements ArrayInterface
{
    /**
     * @var CloudServiceInterface
     */
    private $cloudService;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        CloudServiceInterface $cloudService,
        Config $config
    ) {
        $this->cloudService = $cloudService;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $files = array_merge(
            $this->cloudService->getList('search', 'stopword'),
            $this->getLocalFiles()
        );

        return $files;
    }

    /**
     * Synonym files
     *
     * @return array
     */
    private function getLocalFiles()
    {
        $options = [];

        $path = $this->config->getStopwordDirectoryPath();

        if (file_exists($path)) {
            $dh = opendir($path);
            while (false !== ($filename = readdir($dh))) {
                if (substr($filename, 0, 1) != '.') {
                    $info = pathinfo($filename);
                    $options[] = [
                        'label' => $info['filename'],
                        'value' => $path . DIRECTORY_SEPARATOR . $filename,
                    ];
                }
            }
        }

        return $options;
    }
}

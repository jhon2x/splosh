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


namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Filter;

use Mirasvit\EmailDesigner\Api\Repository\TemplateRepositoryInterface;
use Mirasvit\EmailDesigner\Api\Service\TemplateProcessorInterface;

class ThemeFilter
{
    /**
     * This variable is set automatically in \Liquid\Filterbank on line #95
     *
     * @var \Liquid\Context
     */
    public $context;
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;
    /**
     * @var TemplateProcessorInterface
     */
    private $templateProcessor;

    /**
     * ThemeFilter constructor.
     *
     * @param TemplateRepositoryInterface $templateRepository
     * @param TemplateProcessorInterface  $templateProcessor
     */
    public function __construct(
        TemplateRepositoryInterface $templateRepository,
        TemplateProcessorInterface $templateProcessor
    ) {
        $this->templateRepository = $templateRepository;
        $this->templateProcessor = $templateProcessor;
    }

    /**
     * Display area by area name.
     *
     * @param string      $area    - area name
     * @param bool|string $default - default content or false
     *
     * @return null|string
     */
    public function area($area, $default = false)
    {
        if ($this->context->get('area_' . $area)) {
            $tplContent = $this->context->get('area_' . $area);

            return $this->templateProcessor->process(
                $this->templateRepository->create(),
                $tplContent,
                $this->context->registers
            );
        }

        if ($this->context->get('preview')) {
            if ($default) {
                return $default;
            }

            return true;
        }

        return '';
    }
}

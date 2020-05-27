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


namespace Mirasvit\EmailDesigner\Api\Data;

interface ThemeInterface
{
    const TABLE_NAME = 'mst_email_designer_theme';

    const ID = 'theme_id';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const TEMPLATE_TEXT = 'template_text';
    const THEME_AREAS = 'theme_areas';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    const AREA_PATTERN_PHP    = "/area\(['\"]([0-9A-Za-z_\-]*)['\"]*/";
    const AREA_PATTERN_LIQUID = "/{{\s?['\"]([0-9A-Za-z_\-]*)['\"]\s?\|\s?area/";

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getTemplateText();

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setTemplateText($text);
}

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

interface TemplateInterface
{
    const TABLE_NAME = 'mst_email_designer_template';

    const ID = 'template_id';
    const TITLE = 'title';
    const SYSTEM_ID = 'system_id';
    const DESCRIPTION = 'description';
    const TEMPLATE_SUBJECT = 'template_subject';
    const TEMPLATE_AREAS = 'template_areas';
    const TEMPLATE_AREAS_SERIALIZED = 'template_areas_serialized';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

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
    public function getTemplateSubject();

    /**
     * @return string
     */
    public function getTemplateText();

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function setTemplateSubject($subject);

    /**
     * Get theme model.
     *
     * @return ThemeInterface
     */
    public function getTheme();

    /**
     * Set theme.
     *
     * @param ThemeInterface $theme
     *
     * @return $this
     */
    public function setTheme(ThemeInterface $theme);

    /**
     * @return int
     */
    public function getThemeId();

    /**
     * @param int $themeId
     *
     * @return $this
     */
    public function setThemeId($themeId);

    /**
     * List editable areas.
     *
     * @return array
     */
    public function getAreas();

    /**
     * Get template areas.
     *
     * @return array
     */
    public function getTemplateAreas();

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setTemplateAreas(array $data);

    /**
     * @return string
     */
    public function getTemplateAreasSerialized();

    /**
     * @param string $dataSerialized
     *
     * @return $this
     */
    public function setTemplateAreasSerialized($dataSerialized);

    /**
     * Get editable area text by code.
     *
     * @param string $code
     * @return string|bool
     */
    public function getAreaText($code);

    /**
     * Set editable area text by code.
     *
     * @param string $code
     * @param string $content
     * @return $this
     */
    public function setAreaText($code, $content);

    /**
     * Retrieve area code of email template based on the its content.
     *
     * @param string $content
     *
     * @return null|string
     */
    public function getAreaCodeByContent($content);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return $this
     */
    public function isSystem();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setSystemId($id);

    /**
     * Import template.
     *
     * @param string $filePath
     *
     * @return $this
     */
    public function import($filePath);

    /**
     * Object data getter
     *
     * If $key is not defined will return all the data as an array.
     * Otherwise it will return value of the element specified by $key.
     * It is possible to use keys like a/b/c for access nested array data
     *
     * If $index is specified it will assume that attribute data is an array
     * and retrieve corresponding member. If data is the string - it will be explode
     * by new line character and converted to array.
     *
     * @param string     $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key = '', $index = null);

    /**
     * If $key is empty, checks whether there's any data in the object
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key
     * @return bool
     */
    public function hasData($key = '');

    /**
     * Overwrite data in the object.
     *
     * The $key parameter can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array  $key
     * @param mixed         $value
     *
     * @return $this
     */
    public function setData($key, $value = null);

    /**
     * Add data to the object.
     *
     * Retains previous data in the object.
     *
     * @param array $arr
     *
     * @return $this
     */
    public function addData(array $arr);
}

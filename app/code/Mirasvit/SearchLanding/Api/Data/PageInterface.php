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
 * @package   mirasvit/module-search-landing
 * @version   1.0.7
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchLanding\Api\Data;

interface PageInterface
{
    const TABLE_NAME = 'mst_search_landing_page';

    const ID = 'page_id';
    const QUERY_TEXT = 'query_text';
    const URL_KEY = 'url_key';
    const TITLE = 'title';
    const META_KEYWORDS = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const LAYOUT_UPDATE = 'layout_update';
    const STORE_IDS = 'store_ids';
    const IS_ACTIVE = 'is_active';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getQueryText();

    /**
     * @param string $value
     * @return $this
     */
    public function setQueryText($value);

    /**
     * @return string
     */
    public function getUrlKey();

    /**
     * @param string $value
     * @return $this
     */
    public function setUrlKey($value);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $value
     * @return $this
     */
    public function setTitle($value);

    /**
     * @return string
     */
    public function getMetaKeywords();

    /**
     * @param string $value
     * @return $this
     */
    public function setMetaKeywords($value);

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @param string $value
     * @return $this
     */
    public function setMetaDescription($value);

    /**
     * @return string
     */
    public function getLayoutUpdate();

    /**
     * @param string $value
     * @return $this
     */
    public function setLayoutUpdate($value);

    /**
     * @return string
     */
    public function getStoreIds();

    /**
     * @param string $value
     * @return $this
     */
    public function setStoreIds($value);

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @param bool $value
     * @return $this
     */
    public function setIsActive($value);
}

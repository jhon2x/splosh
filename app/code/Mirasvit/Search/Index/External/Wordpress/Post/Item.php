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


namespace Mirasvit\Search\Index\External\Wordpress\Post;

class Item extends \Magento\Framework\DataObject
{
    /**
     * @return string
     */
    public function getUrl()
    {
        $url = $this->getIndex()->getModel()->getProperty('url_template');

        $data = $this->getData();

        $data['year'] = date('Y', strtotime($data['post_date']));
        $data['monthnum'] = date('m', strtotime($data['post_date']));
        $data['day'] = date('d', strtotime($data['post_date']));

        foreach ($data as $key => $value) {
            $key = strtolower($key);
            if (is_scalar($value)) {
                $url = str_replace('{' . $key . '}', $value, $url);
            }
        }

        return $url;
    }

    /**
     * @return string
     */
    public function getTeaser()
    {
        $contents = explode('<!--more-->', $this->getData('post_content'));

        $teaser = strip_tags($contents[0]);

        return $teaser;
    }
}

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
 * @package   mirasvit/module-search-mysql
 * @version   1.0.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchMysql\Adapter;

use Magento\Framework\Search\RequestInterface;

class Mapper extends \Magento\Framework\Search\Adapter\Mysql\Mapper
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(RequestInterface $request)
    {
        $select = parent::buildQuery($request);

        if (is_array($request->getFrom())) {
            $select->limit($request->getSize(), 0);
        }

        return $select;
    }
}

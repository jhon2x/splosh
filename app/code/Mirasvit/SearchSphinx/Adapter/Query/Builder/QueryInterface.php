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
 * @package   mirasvit/module-search-sphinx
 * @version   1.1.49
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\SearchSphinx\Adapter\Query\Builder;

use Mirasvit\SearchSphinx\SphinxQL\SphinxQL;

interface QueryInterface
{
    /**
     * @param SphinxQL                                         $select
     * @param \Magento\Framework\Search\Request\QueryInterface $query
     * @return SphinxQL
     */
    public function build(
        SphinxQL $select,
        \Magento\Framework\Search\Request\QueryInterface $query
    );
}

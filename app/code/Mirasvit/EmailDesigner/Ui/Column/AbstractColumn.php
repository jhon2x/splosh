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



namespace Mirasvit\EmailDesigner\Ui\Column;


use Magento\Ui\Component\Listing\Columns\Column;

abstract class AbstractColumn extends Column
{
    /**
     * Prepare data for concrete item.
     *
     * @param array $item
     *
     * @return array
     */
    abstract protected function prepareItem(array $item);

    /**
     * Prepare data for grid columns.
     *
     * {@inheritDoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getName()] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }
}

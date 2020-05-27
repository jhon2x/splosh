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



namespace Mirasvit\SearchLanding\Ui\Page\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\SearchLanding\Api\Repository\PageRepositoryInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->pageRepository = $pageRepository;
        $this->collection = $this->pageRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $result = [];

        foreach ($this->pageRepository->getCollection() as $page) {
            $pageData = $page->getData();

            $result[$page->getId()] = $pageData;
        }

        return $result;
    }
}

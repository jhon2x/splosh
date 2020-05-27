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



namespace Mirasvit\Search\Index\Aheadworks\Blog\Post;

use Mirasvit\Search\Api\Data\Index\DataMapperInterface;
use Mirasvit\Search\Service\ContentService;

class DataMapper implements DataMapperInterface
{
    private $contentService;

    public function __construct(
        ContentService $contentService
    ) {
        $this->contentService = $contentService;
    }

    /**
     * {@inheritdoc}
     */
    public function map(array $documents, $dimensions, $indexIdentifier)
    {
        $storeId = current($dimensions)->getValue();

        foreach ($documents as $id => $doc) {
            foreach ($doc as $key => $value) {
                if (is_array($value)) {
                    $value                = implode(', ', $value);
                    $documents[$id][$key] = $value;
                }

                $documents[$id][$key] .= ' ' . $this->contentService->processHtmlContent($storeId, $value);
            }
        }

        return $documents;
    }
}

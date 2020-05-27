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
 * @package   mirasvit/module-misspell
 * @version   1.0.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Misspell\Provider;

use Mirasvit\Misspell\Api\ProviderInterface;

class Mysql implements ProviderInterface
{
    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var Suggester
     */
    private $suggester;

    public function __construct(
        Indexer $indexer,
        Suggester $suggester
    ) {
        $this->indexer = $indexer;
        $this->suggester = $suggester;
    }

    /**
     * {@inheritdoc}
     */
    public function reindex()
    {
        $this->indexer->reindex();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function suggest($phrase)
    {
        return $this->suggester->getSuggest($phrase);
    }
}

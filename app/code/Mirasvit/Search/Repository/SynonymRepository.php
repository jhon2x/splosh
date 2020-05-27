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



namespace Mirasvit\Search\Repository;

use Magento\Framework\Exception\NoSuchEntityException;
use Mirasvit\Search\Api\Data\SynonymInterface;
use Mirasvit\Search\Api\Repository\SynonymRepositoryInterface;
use Mirasvit\Search\Api\Data\SynonymInterfaceFactory;
use Mirasvit\Search\Model\ResourceModel\Synonym\CollectionFactory as SynonymCollectionFactory;

class SynonymRepository implements SynonymRepositoryInterface
{
    /**
     * @var SynonymInterfaceFactory
     */
    private $synonymFactory;

    /**
     * @var SynonymCollectionFactory
     */
    private $synonymCollectionFactory;

    public function __construct(
        SynonymInterfaceFactory $synonymFactory,
        SynonymCollectionFactory $synonymCollectionFactory
    ) {
        $this->synonymFactory = $synonymFactory;
        $this->synonymCollectionFactory = $synonymCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->synonymCollectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->synonymFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        /** @var \Mirasvit\Search\Model\Synonym $synonym */
        $synonym = $this->create();
        $synonym->load($id);

        if (!$synonym->getId()) {
            throw NoSuchEntityException::singleField(SynonymInterface::ID, $id);
        }

        return $synonym;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(SynonymInterface $synonym)
    {
        /** @var \Mirasvit\Search\Model\Synonym $synonym */
        $synonym->delete();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function save(SynonymInterface $synonym)
    {
        /** @var \Mirasvit\Search\Model\Synonym $synonym */

        $synonyms = $synonym->getSynonyms();
        $synonyms = array_unique(array_filter(explode(',', $synonyms)));

        foreach ($synonyms as $key => $term) {
            $term = trim(strtolower($term));

            if (strlen($term) == 0) {
                throw new \Exception(__('The length of synonym must be greater than 1.'));
            }

            $synonyms[$key] = $term;
        }

        $synonym->setTerm(trim(strtolower($synonym->getTerm())));
        $synonym->setSynonyms(implode(',', $synonyms));

        //        if (count(explode(' ', $synonym->getTerm())) != 1) {
        //            throw new \Exception(__('Term "%1" can contain only one word.', $synonym->getTerm()));
        //        }

        $synonym->save();

        return $this;
    }
}

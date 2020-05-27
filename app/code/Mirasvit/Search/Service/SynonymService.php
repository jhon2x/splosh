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



namespace Mirasvit\Search\Service;

use Mirasvit\Search\Api\Data\SynonymInterface;
use Mirasvit\Search\Api\Repository\SynonymRepositoryInterface;
use Mirasvit\Search\Api\Service\CloudServiceInterface;
use Mirasvit\Search\Api\Service\SynonymServiceInterface;

class SynonymService implements SynonymServiceInterface
{
    /**
     * @var SynonymRepositoryInterface
     */
    private $synonymRepository;

    /**
     * @var CloudServiceInterface
     */
    private $cloudService;

    private static $complexSynonyms;

    public function __construct(
        SynonymRepositoryInterface $synonymRepository,
        CloudServiceInterface $cloudService
    ) {
        $this->synonymRepository = $synonymRepository;
        $this->cloudService = $cloudService;
    }

    /**
     * {@inheritdoc}
     */
    public function getSynonyms(array $terms, $storeId)
    {
        $result = [];

        $collection = $this->synonymRepository->getCollection();

        foreach ($terms as $term) {
            $collection->getSelect()
                ->orWhere('term = ?', $term);
        }

        /** @var SynonymInterface $model */
        foreach ($collection as $model) {
            $synonyms = explode(',', $model->getSynonyms());

            foreach ($terms as $term) {
                if ($model->getTerm() === $term) {
                    $result[$term] = $synonyms;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getComplexSynonyms($storeId)
    {
        if (!self::$complexSynonyms) {
            self::$complexSynonyms = $this->synonymRepository->getCollection();

            self::$complexSynonyms->getSelect()
                ->where("term like '% %'");
        }

        return self::$complexSynonyms;
    }

    /**
     * {@inheritdoc}
     */
    public function import($file, $storeIds)
    {
        $result = [
            'synonyms' => 0,
            'total'    => 0,
            'errors'   => 0,
        ];

        if (file_exists($file)) {
            $content = file_get_contents($file);
        } else {
            $content = $this->cloudService->get('search', 'synonym', $file);
        }

        if (!$content) {
            yield false;
        } else {
            $synonyms = \Zend_Config_Yaml::decode($content);

            if (!is_array($storeIds)) {
                $storeIds = [$storeIds];
            }

            foreach ($storeIds as $storeId) {
                $result['total'] = count($synonyms);

                yield $result;

                foreach ($synonyms as $synonym) {
                    try {
                        $model = $this->synonymRepository->create()
                            ->setTerm($synonym['term'])
                            ->setSynonyms($synonym['synonyms'])
                            ->setStoreId($storeId);

                        $this->synonymRepository->save($model);

                        $result['synonyms']++;
                    } catch (\Exception $e) {
                        $result['errors']++;
                    }

                    yield $result;
                }
            }

            yield $result;
        }
    }
}

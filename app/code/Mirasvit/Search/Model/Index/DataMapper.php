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



namespace Mirasvit\Search\Model\Index;

use Mirasvit\Search\Api\Data\Index\DataMapperInterface;
use Mirasvit\Search\Api\Service\SynonymServiceInterface;
use Mirasvit\Search\Model\Config;

class DataMapper implements DataMapperInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var SynonymServiceInterface
     */
    private $synonymService;

    public function __construct(
        Config $config,
        SynonymServiceInterface $synonymService
    ) {
        $this->config = $config;
        $this->synonymService = $synonymService;
    }

    /**
     * {@inheritdoc}
     */
    public function map(array $documents, $dimensions, $indexIdentifier)
    {
        foreach ($documents as $id => $doc) {
            $documents[$id] = $this->recursiveMap($doc);
        }

        return $documents;
    }

    /**
     * @param array|string $data
     * @return array|string
     */
    public function recursiveMap($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->recursiveMap($value);
            }
        } elseif (is_string($data)) {
            $string = strtolower(strip_tags($data));

            $expressions = $this->config->getLongTailExpressions();

            foreach ($expressions as $expr) {
                $matches = null;
                preg_match_all($expr['match_expr'], $string, $matches);

                foreach ($matches[0] as $math) {
                    $math = preg_replace($expr['replace_expr'], $expr['replace_char'], $math);
                    $string .= ' ' . $math;
                }
            }

            $complexSynonyms = $this->synonymService->getComplexSynonyms(0);

            foreach ($complexSynonyms as $synonym) {
                if (strpos($string, $synonym->getTerm()) !== false) {
                    $string .= ' ' . $synonym->getSynonyms();
                }

                $terms = explode(',', $synonym->getSynonyms());
                foreach ($terms as $term) {
                    if (strpos($string, $term) !== false) {
                        $string .= ' ' . $synonym->getTerm();
                    }
                }
            }

            $string = preg_replace('/\s\s+/', ' ', $string);

            return ' ' . $string . '';
        }

        return $data;
    }
}

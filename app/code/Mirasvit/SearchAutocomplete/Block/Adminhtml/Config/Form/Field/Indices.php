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
 * @package   mirasvit/module-search-autocomplete
 * @version   1.1.96
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchAutocomplete\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Mirasvit\SearchAutocomplete\Api\Data\IndexInterface;
use Mirasvit\SearchAutocomplete\Api\Repository\IndexRepositoryInterface;
use Mirasvit\SearchAutocomplete\Helper\Data as DataHelper;

/**
 * @method AbstractElement getElement()
 * @method $this setElement(AbstractElement $element)
 */
class Indices extends Field
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    public function __construct(
        IndexRepositoryInterface $indexService,
        Context $context
    ) {
        $this->indexRepository = $indexService;

        return parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->setTemplate('Mirasvit_SearchAutocomplete::config/form/field/indices.phtml');
    }

    /**
     * {@inheritdoc}
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);

        return $this->_toHtml();
    }

    /**
     * Available indexes
     *
     * @return IndexInterface[]
     */
    public function getIndices()
    {
        $indices = $this->indexRepository->getIndices();

        foreach ($indices as $index) {
            $index->addData([
                'is_active' => $this->getValue($index, 'is_active'),
                'limit'     => intval($this->getValue($index, 'limit')),
                'order'     => intval($this->getValue($index, 'order')),
            ]);
        }

        usort($indices, function ($a, $b) {
            return (int)$a->getOrder() - (int)$b->getOrder();
        });

        return $indices;
    }

    /**
     * Index name
     *
     * @param IndexInterface $index
     * @return string
     */
    public function getNamePrefix($index)
    {
        return $this->getElement()->getName() . '[' . $index->getIdentifier() . ']';
    }

    /**
     * @param IndexInterface $index
     * @param string $item
     * @return string
     */
    public function getValue($index, $item)
    {
        $identifier = $index->getIdentifier();

        if ($this->getElement()->getData('value') && is_array($this->getElement()->getData('value'))) {
            $values = $this->getElement()->getData('value');
            if (isset($values[$identifier]) && isset($values[$identifier][$item])) {
                return $values[$identifier][$item];
            }
        }

        return false;
    }
}

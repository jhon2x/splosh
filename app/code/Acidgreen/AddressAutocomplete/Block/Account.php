<?php

namespace Acidgreen\AddressAutocomplete\Block;

class Account extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var array
	 */
	protected $jsLayout;

	/**
	 * @var \Acidgreen\AddressAutocomplete\Model\AutocompleteConfigProvider
	 */
	protected $configProvider;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Acidgreen\AddressAutocomplete\Model\AutocompleteConfigProvider $configProvider,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
		$this->configProvider = $configProvider;
	}

	/**
	 * @return string
	 */
	public function getJsLayout()
	{
		return \Zend_Json::encode($this->jsLayout);
	}

	public function getCheckoutConfig()
	{
		return $this->configProvider->getConfig();
	}
}
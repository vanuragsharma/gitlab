<?php
namespace Yalla\Theme\Plugin\Catalog\Model;

class Config
{
	/**
     * Adding new sort option
     *
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param [] $options
     * @return []
     */
	public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options) {
		unset($options['name']);
		unset($options['price']);
		
		$options['name_asc'] = __('Product Name (A – Z)');
		$options['name_desc'] = __('Product Name (Z – A)');
		$options['low_to_high'] = __('Price (Low – High)');
		$options['high_to_low'] = __('Price (High – Low)');
		
		return $options;
	}
}

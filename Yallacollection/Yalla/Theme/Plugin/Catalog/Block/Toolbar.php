<?php
namespace Yalla\Theme\Plugin\Catalog\Block;

class Toolbar{

	public function aroundSetCollection(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject, \Closure $proceed, $collection) {
		$currentOrder = $subject->getCurrentOrder();
		$result = $proceed($collection);

		if ($currentOrder) {
			if ($currentOrder == 'high_to_low') {
				$subject->getCollection()->setOrder('price', 'desc');
			} elseif ($currentOrder == 'low_to_high') {
				$subject->getCollection()->setOrder('price', 'asc');
			}
			if ($currentOrder == 'name_asc') {
				$subject->getCollection()->setOrder('name', 'asc');
			} elseif ($currentOrder == 'name_desc') {
				$subject->getCollection()->setOrder('name', 'desc');
			}
		}else{
			$subject->getCollection()->getSelect()->reset('order');
			$subject->getCollection()->setOrder('price', 'asc');
		}
		return $result;
	}
}

<?php

namespace Magepow\Flipbook\Model\Widget\Config;

use Magepow\Flipbook\Model\ResourceModel\Flip\CollectionFactory;

class ThemeLayout implements \Magento\Framework\Option\ArrayInterface
{

	protected $scopeConfig;
	protected $conllectionFactory;

	public function toOptionArray()
	{
		return [1=>'Full Width','Two Column','Three Column','Four Column'];
	}

}
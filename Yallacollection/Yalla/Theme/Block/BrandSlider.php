<?php

namespace Yalla\Theme\Block;

class BrandSlider extends \Magento\Framework\View\Element\Template
{
    protected $helper;

	public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Mageplaza\Shopbybrand\Helper\Data $helper)
	{
		$this->helper = $helper;
		parent::__construct($context);

	}
    
    protected function _prepareLayout(){
		parent::_prepareLayout();
		$this->setTemplate('Brand/slider.phtml');
		return $this;
	}


	public function getCategories() 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$catId = 33; // Parent Category ID Brand
		$subcategory = $objectManager->create('Magento\Catalog\Model\Category')->load($catId);

		$subcats = $subcategory->getChildrenCategories();
        return $subcats;
    }
    
    public function getCollection($type = null, $option = null)
    {

        return $this->helper()->getBrandList($type, $option);
    }

    public function helper()
    {
        return $this->helper;
    }

}
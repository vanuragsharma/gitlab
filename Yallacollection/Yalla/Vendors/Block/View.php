<?php
	namespace Yalla\Vendors\Block;
	class View extends \Magento\Framework\View\Element\Template {
	protected $helper;
	
	public function __construct(\Magento\Framework\View\Element\Template\Context $context,\Yalla\Vendors\Helper\Data $helperData){
	$this->helper = $helperData;
	    parent::__construct($context);
	}
    protected function _prepareLayout(){
		parent::_prepareLayout();
		$this->setTemplate('view.phtml');
		return $this;
	}
	
	public function getBannerUrl($banner){
        return $this->helper->getBannerUrl($banner);
}
	public function getVendors(){
	
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$model = $objectManager->create('\Yalla\Vendors\Model\Vendors');
		
		$multiselectupdate =$model->getCollection() ->addFieldToFilter('status',1);
		return $multiselectupdate->getData();
		
       // $this->helper = $getBannerUrl;
     //   return $this->helper->getBannerUrl();

	}
	
    }
	
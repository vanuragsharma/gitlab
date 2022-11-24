<?php
	namespace Yalla\Magentocatalog\Block;
	class View extends \Magento\Framework\View\Element\Template
	{
    protected $_registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout(){
		parent::_prepareLayout();
		$this->setTemplate('view.phtml');
		return $this;
	}
    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }
    
    public function getChildrenCategory($categoryId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 

        $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
        $category = $categoryFactory->create()->load($categoryId);

        return $childrenCategories = $category->getChildrenCategories()->addAttributeToSelect('image')->addAttributeToSelect('app_banner');
    }


}
?>


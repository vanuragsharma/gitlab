<?php

namespace BoostMyShop\Erp\Controller\Adminhtml;

abstract class Products extends \Magento\Backend\App\AbstractAction
{
    protected $_coreRegistry;
    protected $_resultLayoutFactory;
    protected $_dir;
    protected $_productFactory;
    protected $_advancedStockConfig;
    protected $_orderPreparationConfig;
    protected $_productAction;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \BoostMyShop\AdvancedStock\Model\Config $advancedStockConfig,
        \BoostMyShop\OrderPreparation\Model\Config $orderPreparationConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Action $productAction
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_dir = $dir;
        $this->_productFactory = $productFactory;
        $this->_advancedStockConfig = $advancedStockConfig;
        $this->_orderPreparationConfig = $orderPreparationConfig;
        $this->_productAction = $productAction;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}

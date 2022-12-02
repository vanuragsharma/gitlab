<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml;

abstract class MassStockEditor extends \Magento\Backend\App\AbstractAction
{
    protected $_coreRegistry;
    protected $_resultLayoutFactory;
    protected $_warehouseItemFactory;
    protected $_stockMovementFactory;
    protected $_backendAuthSession;
    protected $_productAction;
    protected $_dir;
    protected $_uploaderFactory;



    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\File\UploaderFactory $uploaderFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_stockMovementFactory = $stockMovementFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_productAction = $productAction;
        $this->_dir = $dir;
        $this->_uploaderFactory = $uploaderFactory;
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

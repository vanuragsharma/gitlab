<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml;

/**
 * Class StockTake
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class StockTake extends \Magento\Backend\App\AbstractAction {

    protected $_coreRegistry;
    protected $_resultJsonFactory;
    protected $_resultLayoutFactory;
    protected $_stockTakeFactory;
    protected $_stockTakeItemCollectionFactory;
    protected $_fileFactory;
    protected $_product;
    protected $_config;
    protected $_uploaderFactory;
    protected $_dir;
    protected $_resultRawFactory;
    protected $_backendAuthSession;
    protected $_httpFactory;
    protected $_productInformation;

    /**
     * StockTake constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \BoostMyShop\AdvancedStock\Model\StockTakeFactory $stockTakeFactory
     * @param \Magento\Framework\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \BoostMyShop\AdvancedStock\Model\StockTakeFactory $stockTakeFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \BoostMyShop\AdvancedStock\Model\ProductInformation $productInformation,
        \BoostMyShop\AdvancedStock\Model\StockTake\CsvExport $csvExport,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\Item\CollectionFactory $stockTakeItemCollectionFactory,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_stockTakeFactory = $stockTakeFactory;
        $this->_stockTakeItemCollectionFactory = $stockTakeItemCollectionFactory;
        $this->_fileFactory = $fileFactory;
        $this->_product = $product;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_dir = $dir;
        $this->_resultRawFactory = $resultRawFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_config = $config;
        $this->_csvExport = $csvExport;
        $this->_httpFactory = $httpFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_productInformation = $productInformation;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    protected function _registerCurrentStockTake(){

        if($id = filter_var($this->getRequest()->getParam('id'), FILTER_VALIDATE_INT)){
            $this->_coreRegistry->register('current_stocktake', $this->_stockTakeFactory->create()->load($id));
        }else{
            $this->_coreRegistry->register('current_stocktake', $this->_stockTakeFactory->create());
        }

    }

}
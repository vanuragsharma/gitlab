<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer;

/**
 * Class AddProductsScan
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer
 * @author    Romain Jourdes <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductsScan extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer
{

    /**
     * @var \BoostMyShop\AdvancedStock\Model\TransferFactory
     */
    protected $_transferFactory;

    /**
     * Apply constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Framework\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \BoostMyShop\AdvancedStock\Model\TransferFactory $transferFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \BoostMyShop\AdvancedStock\Model\TransferFactory $transferFactory
    ) {
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $warehouseFactory, $uploaderFactory, $fileFactory, $httpFactory, $product, $dir);
        $this->_transferFactory = $transferFactory;
    }

    public function execute()
    {
        $id = filter_var($this->getRequest()->getParam('id'));
        $model = $this->_transferFactory->create()->load($id);

        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This transfer no longer exists.'));
            $this->_redirect('*/*/index');
            return;
        }

        $this->_coreRegistry->register('current_transfer', $model);
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Add products with scanner [ %1 ]', $model->getst_reference()));
        $this->_view->renderLayout();
    }
}

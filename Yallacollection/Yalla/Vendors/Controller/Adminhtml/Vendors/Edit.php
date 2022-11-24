<?php
namespace Yalla\Vendors\Controller\Adminhtml\Vendors;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Yalla\Vendors\Model\vendorsFactory;

class Edit extends \Magento\Backend\App\Action
{
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Vendors model factory
     *
     * @var \Yalla\Vendors\Model\StoresFactory
     */
    protected $_vendorsFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param VendorsFactory $vendorsFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        VendorsFactory $vendorsFactory
    ) {
       parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_vendorsFactory = $vendorsFactory;
    }

    /**
     * store access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Yalla_Vendors::vendors');
    }
   /**
     * @return void
     */
   public function execute()
   {
      $bannerId = $this->getRequest()->getParam('id');
        /** @var \Yalla\Vendors\Model\Stores $model */
        $model = $this->_vendorsFactory->create();

        if ($bannerId) {
            $model->load($bannerId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This banner no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        // Restore previously entered form data from session
        $data = $this->_session->getBannerData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->_coreRegistry->register('yalla_vendor', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Yalla_Vendors::main_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Vendor'));

        return $resultPage;
   }
}

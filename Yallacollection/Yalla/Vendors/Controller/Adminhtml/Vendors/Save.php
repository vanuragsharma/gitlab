<?php

namespace Yalla\Vendors\Controller\Adminhtml\Vendors;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Yalla\Vendors\Model\VendorsFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Yalla\Vendors\Model\VendorProductsFactory;

class Save extends \Magento\Backend\App\Action {

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
     * @var \Yalla\Vendors\Model\VendorsFactory
     */
    protected $_vendorsFactory;
    
    /**
     * Vendor Products model factory
     *
     * @var \Yalla\Vendors\Model\VendorProductsFactory
     */
    protected $_vendorProductsFactory;

    /**
     * Locale Date/Timezone
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_date;

    /**
     * File System
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * File Upload Factory
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * List of allowed files
     * @var array
     */
    protected $_allowedExtensions = ['jpeg','jpg','png']; // to allow file upload types 

    /**
     * File type input field name
     * @var string
     */
    protected $_fileId = 'vendors[vendor_file_name_val]';

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param VendorsFactory $vendorsFactory
     */
    public function __construct(
    Context $context, Registry $coreRegistry, PageFactory $resultPageFactory, VendorsFactory $vendorsFactory, VendorProductsFactory $vendorProductsFactory, 
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date, Filesystem $_fileSystem, 
            UploaderFactory $_uploaderFactory
    ) {
        $this->_fileSystem = $_fileSystem;
        $this->_uploaderFactory = $_uploaderFactory;
        parent::__construct($context);
        $this->_date = $date;
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_vendorsFactory = $vendorsFactory;
        $this->_vendorProductsFactory = $vendorProductsFactory;
    }

    /**
     * vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Yalla_Vendors::vendors');
    }

    /**
     * @return void
     */
    public function execute() {
        $isPost = $this->getRequest()->getPost();
        
        if ($isPost) {
            $destinationPath = $this->getDestinationPath(); // Return upload folder path
            
            $vendorModel = $this->_vendorsFactory->create();
            $vendorId = $this->getRequest()->getParam('id');

            $formData = $this->getRequest()->getParam('vendors');
            if ($vendorId) {
                $vendorModel->load($vendorId);
                $formData['updated_at'] = $this->_date->date()->format('Y-m-d H:i:s');
            } else {
                $formData['created_at'] = $this->_date->date()->format('Y-m-d H:i:s');
            }
            
            try {
            //var_dump($formData);die;
                $vendorModel->setData($formData);
                // Save vendor
                $vendorModel->save();

				 if($vendorModel->getId() && !empty($formData['vendor_products'])){
				 	$product_ids = explode(',', $formData['vendor_products']);
				 	
                    foreach($product_ids as $product_id){
					    $vendorProductsModel = $this->_vendorProductsFactory->create();
					    $vendorProductsModel->setVendorId($vendorModel->getId());
					    $vendorProductsModel->setProductId($product_id);
					    $vendorProductsModel->save();
                    }
                }
                
                // Display success message
                $this->messageManager->addSuccess(__('The vendor has been saved.'));

                // Check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $vendorModel->getId(), '_current' => true]);
                    return;
                }

                // Go to grid page
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
			
            $this->_getSession()->setFormData($formData);
            $this->_redirect('*/*/edit', ['id' => $vendorModel->getId()]);
        }
    }
    
    /**
     * Get upload folder path
     * @return string
     */
    public function getDestinationPath()
    {
        return $this->_fileSystem
            ->getDirectoryWrite(DirectoryList::MEDIA)
            ->getAbsolutePath('/vendor_logo/');
    }

}

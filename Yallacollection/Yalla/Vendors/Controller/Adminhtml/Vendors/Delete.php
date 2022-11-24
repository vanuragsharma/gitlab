<?php
namespace Yalla\Vendors\Controller\Adminhtml\Vendors;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Yalla\Vendors\Model\Vendors;

class Delete extends \Magento\Backend\App\Action
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
     * @var \Tutorial\SimpleStores\Model\VendorsFactory
     */
    protected $_VendorsFactory;

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
        Vendors $vendorsFactory
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
      $bannerId = (int) $this->getRequest()->getParam('id');
      
      if ($bannerId) {
         /** @var $bannerModel \Yalla\Vendors\Model\Vendors */
         $bannerModel = $this->_vendorsFactory;
         $bannerModel->load($bannerId);

         // Check this banner exists or not
         if (!$bannerModel->getId()) {
            $this->messageManager->addError(__('This banner no longer exists.'));
         } else {
               try {
                  // Delete banner
                  $bannerModel->delete();
                  $this->messageManager->addSuccess(__('The banner has been deleted.'));

                  // Redirect to grid page
                  $this->_redirect('*/*/');
                  return;
               } catch (\Exception $e) {
                   $this->messageManager->addError($e->getMessage());
                   $this->_redirect('*/*/edit', ['id' => $bannerModel->getId()]);
               }
            }
      }
   }
}

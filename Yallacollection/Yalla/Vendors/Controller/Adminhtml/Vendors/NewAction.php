<?php
namespace Yalla\Vendors\Controller\Adminhtml\Vendors;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Yalla\Vendors\Model\Vendors;

class NewAction extends \Magento\Backend\App\Action
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
     * @var \Yalla\Vendors\Model\Vendors
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
        Vendors $vendorsFactory
    ) {
       parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_vendorsFactory = $vendorsFactory;
    }

    /**
     * banner access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Yalla_Vendors::vendors');
    }
   /**
     * Create new action
     *
     * @return void
     */
   public function execute()
   {
      $this->_forward('edit');
   }
}

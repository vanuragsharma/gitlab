<?php

namespace Yalla\Vendors\Controller\Adminhtml\Vendors;

class Orderitems extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * banner access rights checking
     *
     * @return bool
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Yalla_Vendors::vendors');
    }

    /**
     * Load the page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {
    	$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('Yalla_Vendors::main_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Vendor'));
        return $resultPage;
    }

}

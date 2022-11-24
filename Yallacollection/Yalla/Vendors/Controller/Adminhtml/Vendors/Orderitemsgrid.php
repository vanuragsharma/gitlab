<?php

namespace Yalla\Vendors\Controller\Adminhtml\Vendors;

class Orderitemsgrid extends \Magento\Backend\App\Action {

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
     * Load the page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {
        $resultPage = $this->resultPageFactory->create();
        
        $block = $resultPage->getLayout()
                ->createBlock('Yalla\Vendors\Block\Adminhtml\Orderitems\Grid')
                ->toHtml();
        $this->getResponse()->setBody($block);
    }

}

<?php

namespace Yalla\Theme\Controller\GiftWrap;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{

    public function __construct(
          \Magento\Framework\App\Action\Context $context,
          \Magento\Framework\View\Result\PageFactory $resultPageFactory,
          \Magento\Store\Model\StoreManagerInterface $storeManager

        ) { 
            parent::__construct($context); 

            $this->resultPageFactory = $resultPageFactory;

            $this->_storeManager=$storeManager;

    }

    public function execute() {
        $storeCode = $this->_storeManager->getStore()->getCode();
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $response = $this->getRequest()->getPostValue('giftwrap');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cartObj = $objectManager->get('\Magento\Checkout\Model\Cart');
        $quote = $cartObj->getQuote();
        $quote->setData('giftwrap', $response);// Fill data
        $quote->save();
        // return $this;
    }
     
}

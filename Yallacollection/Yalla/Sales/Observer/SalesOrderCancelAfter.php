<?php

	namespace Yalla\Sales\Observer;

    use \Magento\Framework\Event\ObserverInterface;
	use \Magento\Framework\Event\Observer; 
   
  //  use Magento\Backend\Model\Auth\Session;
  //  use Magento\Framework\View\Element\Template;

	class SalesOrderCancelAfter implements ObserverInterface 
    {
   /*   protected $authSession;

      public function __construct(Session $authSession)
      {
        $this->authSession = $authSession;
      }
      public function getCurrentUser()
      {
        return $this->authSession->getUser();
      } */

      public function __construct( 

       \Yalla\Theme\Helper\Data $dataHelper

    ) { 

        $this->dataHelper = $dataHelper;
    
    }

      public function execute(Observer $observer)
      {	
     	try {
            $order = $observer->getEvent()->getOrder();
			      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		        $helper = $objectManager->get('Yalla\Theme\Helper\Data');
            //$helper = $this->dataHelper->webEngageTrackEvent();
			      $helper->webEngageTrackEvent('cancelled', $order);

        } catch (\Exception $e) {
            return false;
        }
         
	}
	
}


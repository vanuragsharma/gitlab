<?php
	
	namespace Yalla\Sales\Observer;

	use Magento\Framework\Event\ObserverInterface;
	use Magento\Framework\Event\Observer;
	use Magento\Framework\Message\ManagerInterface;
	use Magento\Framework\Exception\LocalizedException;
   // use Magento\Backend\Model\Auth\Session;
   // use Magento\Framework\View\Element\Template;

class SalesOrderCreditmemoSaveAfter implements ObserverInterface
{
    /*  protected $authSession;

      public function __construct(Session $authSession)
      {
        $this->authSession = $authSession;
      }
      public function getCurrentUser()
      {
        return $this->authSession->getUser();
      } */

      public function execute(Observer $observer)
      {	
     	try {
            $creditmemo = $observer->getEvent()->getCreditmemo();
		   // $order = $creditmemo->getOrder();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		    $helper = $objectManager->get('Yalla\Theme\Helper\Data');
			$helper->webEngageTrackEvent('creditmemo', $creditmemo);
             
        } catch (\Exception $e) {
            return false;
        }
	}
}

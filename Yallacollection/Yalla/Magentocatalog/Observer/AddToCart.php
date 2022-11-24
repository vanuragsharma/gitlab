<?php
namespace Yalla\Magentocatalog\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class AddToCart implements ObserverInterface
{
    protected $_messageManager;   
    protected $_productFactory;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        CheckoutSession $checkoutSession,
        \Magento\Catalog\Model\ProductFactory $_productFactory
    )
    {
        $this->_messageManager = $messageManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_productFactory = $_productFactory;
    }
 
    /**
     * add to cart event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$added_item = $observer->getRequest()->getParams('product');
		$error = '';
    	if(isset($added_item['product']) && !empty($added_item['product'])){

			$product = $this->_productFactory->create()->load($added_item['product']);
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$itemHelper = $objectManager->create('Mageplaza\PreOrder\Helper\Item');
			$isPreOrder = $itemHelper->isApplyForProduct($product);

			$items = $this->_checkoutSession->getQuote()->getAllItems();
			// Added product is preorder
			if($isPreOrder){
				foreach($items as $item){
					$product = $item->getProduct();
					$isPreOrder = $itemHelper->isApplyForProduct($product);
					if(!$isPreOrder){
						$error = "<p>".__('You have regular items in your cart, please complete your order to add a Pre-Order item to your carts')."<p><div class='preorderbtn'><a href='https://yallatoys.com/checkout/cart/'><button class='preorderbtns'>Go To cart</button></a></div>";
					}
				}
			}else{
				// Added product is not preorder
				foreach($items as $item){
					$product = $item->getProduct();
					$isPreOrder = $itemHelper->isApplyForProduct($product);
					if($isPreOrder){
						$error = "<p>".__('You have Pre-Order items in your cart, please complete your order for adding other items')."</p><div class='preorderbtn'><a href='https://yallatoys.com/checkout/cart/'><button class='preorderbtns'>Go To cart</button></a></div>";
					}
				}
			}
			
			if($error){
				$response = array("backUrl" => [], "success" => false, "html_popup" => $error, "html_class" => "preorder");
				echo json_encode($response);
				exit;
			}
    	}
 
        return $this;
    }
}

<?php
/**
  * @category   Mageants Reorder
  * @package    Mageants_Reorder
  * @copyright  Copyright (c) 2017 Mageants
  * @author     Mageants Team <support@Mageants.com>
  */

namespace Mageants\EventManager\Controller\Index;
 
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;


 
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Addcart extends \Magento\Framework\App\Action\Action
{

    protected $formKey;   
    protected $cart;
    protected $product;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->product = $product;
        $this->_storeManager = $storeManager;      
        parent::__construct($context);
    }

    public function execute()
    { 
       $selectedItems = $this->getRequest()->getPost('productassign');      
        $selectedItems = explode(",",$selectedItems);
        try{
        foreach ($selectedItems as $key => $selectedItem) {

            $params = array(
                'form_key' => $this->formKey->getFormKey(),
                'product_id' => $selectedItem, //product Id
                'qty'   =>1 //quantity of product                
            );
            $_product = $this->product->create()->load($selectedItem);
           
       
            $this->cart->addProduct($_product, $params);
            
        }
           $this->cart->save();
           $this->messageManager->addSuccess(__('Add to cart successfully.'));
           $this->_redirect('checkout/cart/index');
             return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
                 $this->messageManager->addException(
                     $e,
                     __('%1', $e->getMessage())
                 );
                  $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                 $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                 return $resultRedirect;
            } catch (\Exception $e) {
                 $this->messageManager->addException($e, $e->getMessage());
                 $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                 $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                 return $resultRedirect;
            }
       
    }
}
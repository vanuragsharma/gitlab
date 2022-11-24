<?php

namespace Yalla\Vendors\Controller\Adminhtml\Vendors;

use \Magento\Framework\Controller\ResultFactory;

class Orderemail extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $_objectManager = null;
    
    protected $_messageManager;
    
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory,
    \Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Framework\App\ResourceConnection $resource,
    \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
        $this->resource = $resource;
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
    	$isPost = $this->getRequest()->getPost();
        
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
   		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
   		
        if ($isPost) {
        	$item_ids = $this->getRequest()->getParam('items_id');
        	if(!count($item_ids)){
        		$this->_messageManager->addErrorMessage('Order items are not selected.');
        		return $resultRedirect;
        		exit;
        	}

        	$ids = implode(',', $item_ids);
        	var_dump($ids);
        	$connection = $this->resource->getConnection();
        	
        	$order_item_table = $connection->getTableName('sales_order_item');
        	$order_table = $connection->getTableName('sales_order');
        	$vendorProductTable = $connection->getTableName('yalla_vendor_products');
  	    	$vendorsTable = $connection->getTableName('yalla_vendors');
        	
        	$query = "SELECT oi.item_id, oi.product_id, oi.sku, oi.order_id, oi.name, oi.qty_ordered, o.increment_id, oi.email_sent, v.vendor_email from ".$order_item_table." as oi 
        		inner join ".$order_table." as o on oi.order_id = o.entity_id 
        		inner join ".$vendorProductTable." as vp on oi.product_id = vp.product_id 
        		inner join ".$vendorsTable." as v on vp.vendor_id = v.vendor_id 
        	 where oi.item_id in(".$ids.")";
        	 
        	$orderItems = $connection->fetchAll($query);
        	$items_to_email = [];
        	foreach($orderItems as $item){
        		var_dump($item);
        		if(!$item['email_sent']){
		    		$items_to_email[$item['vendor_email']][] = [
		    			'order_number' => $item['increment_id'],
		    			'sku' => $item['sku'],
		    			'name' => $item['name'],
		    			'qty' => $item['qty_ordered']
		    		];
        		}
        	}

			if(!count($items_to_email)) {
				$this->_messageManager->addErrorMessage('No order item to send.');
				return $resultRedirect;
				exit;
			}

			$subject = "New Orders from Yallatoys";
            $from = "brajendra@einfachub.com";
            $headers = "From: " . $from . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            
            foreach($items_to_email as $email => $vendor_orders){
        		$to = $email;
        		$headers .= "Reply-To: " . $to . "\r\n";
        		$body = $this->emailBody($vendor_orders);
        		mail($to, $subject, $body, $headers);
            }
            
			$sql = "Update " . $order_item_table . " set email_sent = 1 where item_id in(".$ids.")";
			$connection->query($sql);
			
			$this->_messageManager->addSuccessMessage('An order email has been sent to vendors.');
			return $resultRedirect;
        }
        
        return $resultRedirect;
    }

	private function emailBody($items){
		$body = '<html><body>';
        $body .= '<div style="max-width: 100%;display:block;">';
        $body .= '<div style="margin-bottom: 20px;margin-bottom: 10px; "><strong>Ordered Items</strong></div>';
        
        $body .= '<table style="border-collapse: collapse">';

        $body .= '<tr>
        			<th style="width: 15%;padding: 15px; border: 1px solid #222;">Order Number</th>
                    <th style="width: 15%;padding: 15px; border: 1px solid #222;">SKU</th>
                    <th style="width: 55%;padding: 15px; border: 1px solid #222;">Product Name</td>
                    <th style="width: 15%;padding: 15px; border: 1px solid #222;">Quantity</td>
                  </tr>';

		foreach($items as $item){
        	$body .= '<tr>
                        <td style="padding: 15px; border: 1px solid #222;text-align:center;">'.$item['order_number'].'</td>
                        <td style="padding: 15px; border: 1px solid #222;text-align:center;">'.$item['sku'].'</td>
                        <td style="padding: 15px; border: 1px solid #222;text-align:center;">'.$item['name'].'</td>
                        <td style="padding: 15px; border: 1px solid #222;text-align:center;">'.(int) $item['qty'].'</td>
                    </tr>';
		}

        $body .= '</table>';

        return $body .= '</body></html>';
	}
}

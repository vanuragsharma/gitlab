<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Replenishment;

use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

class PurchaseOrder extends \Magento\Backend\App\Action
{
    protected $logger;
    protected $resultJsonFactory;
    protected $_orderFactory;
    protected $_urlInterface;
    protected $_warehouse;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        \BoostMyShop\Supplier\Model\OrderFactory $orderFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \BoostMyShop\Supplier\Model\Source\Warehouse $warehouse
    ) {

        $this->logger            = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_orderFactory = $orderFactory;
        $this->_urlInterface = $urlInterface;
        $this->_warehouse = $warehouse;
        parent::__construct($context);
    }

    public function execute()
    {
        try
        {
            $resultJson = $this->resultJsonFactory->create();
                 $data = $this->getRequest()->getParams();
                 $supplier = $data['sup_data'];
                 if (!isset($data['products']))
                     throw new \Exception('No products selected');

                 $productsJson = $data['products'];
                 $supData = explode('&', $supplier);
                 $supplierId = $supData[0];
                 $type = $supData[1];

                 $products = json_decode($productsJson, true);

                 $tmp = [];
                foreach($products[$supplierId] as $item)
                {
                     if($type == 'low_stock') {
                         if(!is_null($item['qty_to_receive']))
                             $tmp[$item['entity_id']] = (int)$item['qty_for_low_stock']-(int)$item['qty_to_receive'];
                         else
                             $tmp[$item['entity_id']] = $item['qty_for_low_stock'];
                     }
                     else if($type == 'back_order') {
                         if(!is_null($item['qty_to_receive']))
                             $tmp[$item['entity_id']] = (int)$item['qty_for_backorder']-(int)$item['qty_to_receive'];
                         else
                             $tmp[$item['entity_id']] = $item['qty_for_backorder'];
                     }
                     else if($type == 'all') {
                         $tmp[$item['entity_id']] = $item['qty_to_order'];
                     }
                 }

                 $products = $tmp;

                //assign first whs
                $warehouseId = '';
                $warehouses = $this->_warehouse->toOptionArray();
                if (isset($warehouses[0]['value']))
                    $warehouseId = $warehouses[0]['value'];

                 $order = $this->_orderFactory->create();
                 $order->applyDefaultData($supplierId);
                 $order->setpo_warehouse_id($warehouseId);
                 $order->save();

                 foreach($products as $productId => $qty)
                 {
                     if ($qty > 0){
                         $order->addProduct($productId, $qty);
                     }
                 }

            $success = 1;
            $msg = $this->_urlInterface->getUrl('supplier/order/edit/po_id/'.$order->getId());
            $this->messageManager->addSuccess(__('Order created.'));

        } catch (\Exception $ex) {
            $resultJson->setStatusHeader(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
                $success = 0;
                $msg = __('An error occurred'.$ex->getMessage());
        }

        return $resultJson->setData(['success'=>$success, 'msg'=>$msg]);
    }
}

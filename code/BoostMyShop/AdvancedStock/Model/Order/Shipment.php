<?php

namespace BoostMyShop\AdvancedStock\Model\Order;

use Magento\Backend\App\Area\FrontNameResolver;

class Shipment
{
    protected $_objectManager;
    protected $_configScope;

    protected $_registry;
    protected $_objectManagerFactory;
    protected $_shipmentRepository;
    protected $_orderItemFactory;
    protected $_extendedOrderItemFactory;
    protected $_stockMovementFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ObjectManagerFactory $objectManagerFactory,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \BoostMyShop\AdvancedStock\Model\ExtendedSalesFlatOrderItemFactory $extendedOrderItemFactory,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory,
        array $data = []
    )
    {
        $this->_registry = $registry;
        $this->_objectManagerFactory = $objectManagerFactory;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_extendedOrderItemFactory = $extendedOrderItemFactory;
        $this->_stockMovementFactory = $stockMovementFactory;
    }

    public function delete($shipmentId, $deleteInProgress = false)
    {
        $shipment = $this->_shipmentRepository->get($shipmentId);
        $order = $shipment->getOrder();

        if(!$shipment->getentity_id())
        {
            throw new \Exception(__('Unable to load shipment with ID %1', $shipmentId));throw new \Exception(__('Unable to load shipment with ID %1', $shipmentId));
        }

        $shipmentItems = [];
        foreach($shipment->getAllItems() as $shipmentItem)
        {
            if(!$this->canDeleteShipmentItem($shipmentItem))
                throw new \Exception(__('Unable to delete shipment with ID %1 as it contains some configurable or bundle product(s)', $shipmentId));

            $shipmentItems[$shipmentItem->getproduct_id()] = $shipmentItem->getqty();
        }

        $this->_registry->register('isSecureArea','true');
        $shipment->delete();
        $this->_registry->unregister('isSecureArea');

        $order
            ->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);

        $inProgressFactory = $this->getObjectManager()->create('BoostMyShop\OrderPreparation\Model\InProgressFactory');
        $orderInProgress = $inProgressFactory->create()->loadByOrderReference($order->getincrement_id());
        if($orderInProgress->getip_id() && $deleteInProgress)
            $orderInProgress->delete();

        foreach($order->getAllItems() as $orderItem)
        {
            $productId = $orderItem->getproduct_id();

            if(array_key_exists($productId, $shipmentItems))
            {
                $qtyCanceled = $shipmentItems[$productId];
                $newShippedQty = max(($orderItem->getqty_shipped() - $qtyCanceled), 0);
                $orderItem
                    ->setqty_shipped($newShippedQty)
                    ->save();

                $extendedOrderItem = $this->_extendedOrderItemFactory->create()->loadByItemId($orderItem->getitem_id());

                $warehouseId = $extendedOrderItem->getesfoi_warehouse_id();

                if($warehouseId)
                {
                    $this->_stockMovementFactory->create()->create(
                        $productId,
                        0,
                        $warehouseId,
                        abs($qtyCanceled),
                        \BoostMyShop\AdvancedStock\Model\StockMovement\Category::adjustment,
                        __('Cancel shipment #%1', $shipment->getincrement_id()),
                       ""
                    );
                }
            }
        }
        $order->save();
    }

    public function canDeleteShipmentItem($shipmentItem)
    {
        $orderItem = $shipmentItem->getOrderItem();

        return $orderItem->getparent_item_id() ? false : true;
    }

    protected function getObjectManager()
    {
        if (null == $this->_objectManager)
        {
            $area = FrontNameResolver::AREA_CODE;
            $this->_configScope = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Config\ScopeInterface::class);
            $this->_configScope->setCurrentScope($area);
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }

        return $this->_objectManager;
    }
}
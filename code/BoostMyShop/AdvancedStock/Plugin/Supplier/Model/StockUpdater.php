<?php

namespace BoostMyShop\AdvancedStock\Plugin\Supplier\Model;

//Rewrite the way supplier module put product in stock for receptions
class StockUpdater
{
    protected $_stockMovementFactory;
    protected $_backendAuthSession;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ){
        $this->_stockMovementFactory = $stockMovementFactory;
        $this->_backendAuthSession = $backendAuthSession;
    }

    public function aroundIncrementStock(\BoostMyShop\Supplier\Model\StockUpdater $subject, $proceed, $productId, $qty, $reason, $po)
    {
        $userId = '?';
        if ($this->_backendAuthSession->isLoggedIn())
            $userId =  $this->_backendAuthSession->getUser()->getId();

        $from = ($qty<0)?$po->getpo_warehouse_id():0;
        $to = ($qty<0)?0:$po->getpo_warehouse_id();
        $this->_stockMovementFactory->create()->create($productId,
                                                        $from,
                                                        $to,
                                                        abs($qty),
                                                        \BoostMyShop\AdvancedStock\Model\StockMovement\Category::purchaseOrder,
                                                        $reason,
                                                        $userId);
    }
}
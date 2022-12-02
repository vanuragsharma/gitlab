<?php

namespace BoostMyShop\AdvancedStock\Model;


class StockMovement extends \Magento\Framework\Model\AbstractModel
{
    protected $_dateTime = null;
    protected $_warehouseItemFactory = null;
    protected $_stockMovementLogs = null;
    protected $_config;

    protected static $_disabledStockMovementWarehouseIds = null;

    protected $_eventPrefix = 'advancedstock_stock_movement';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \BoostMyShop\AdvancedStock\Model\StockMovementLogs $logs,
        \BoostMyShop\AdvancedStock\Model\Config $config,

        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_dateTime = $dateTime;
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_stockMovementLogs = $logs;
        $this->_config = $config;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\StockMovement');
    }

    public function updateProductQuantity($productId, $warehouseId, $originalQty, $targetQty, $comments, $userId)
    {
        if ($targetQty < 0)
            throw new \Exception('Negative quantity is not allowed');

        $from = ($originalQty > $targetQty ? $warehouseId : 0);
        $to = ($originalQty < $targetQty ? $warehouseId : 0);
        $qty = abs($targetQty - $originalQty);

        return $this->create($productId, $from, $to, $qty, \BoostMyShop\AdvancedStock\Model\StockMovement\Category::adjustment, $comments, $userId);
    }

    public function create($productId, $from, $to, $qty, $category, $comments, $userId, $additionnal = [])
    {
        if ($qty == 0)
            return;
        if (!$from && !$to)
            throw new \Exception('Can not create a stock movement with no direction');
        if (!$productId)
            throw new \Exception('No product id available for stock movement creation');
        if (!$qty || ($qty <= 0))
            throw new \Exception('Quantity incorrect for stock movement');

        $newStockLevel = null;

        if ($from)
        {
            $wi = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $from);

            //check final qty
            if ($wi->getwi_physical_quantity() - $qty < 0){
                throw new \Exception('Negative quantity is not allowed, stock movement can not be created for product id '.$productId.', original qty is '.$wi->getwi_physical_quantity().' and qty is '.$qty);
            }

            //get new stock level
            if(!$to){
                $newStockLevel = $wi->getwi_physical_quantity() - $qty;
            }
        }

        //get new stock level
        if(!$from && $to)
        {
            $wi = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $to);

            $newStockLevel = $wi->getwi_physical_quantity() + $qty;
        }

        //if movement on whs with stock movements disabled, we do not create the stock movement
        $createMovement = true;
        if (($from && $this->hasStockMovementDisabled($from) || !$from) && ($to && $this->hasStockMovementDisabled($to) || !$to))
        {
            $createMovement = false;
        }

        if ($createMovement)
        {
            $this->setsm_created_at($this->_dateTime->gmtDate());
            $this->setsm_product_id($productId);
            $this->setsm_from_warehouse_id($from);
            $this->setsm_to_warehouse_id($to);
            $this->setsm_qty($qty);
            $this->setsm_category($category);
            $this->setsm_comments($comments);
            $this->setsm_user_id($userId);
            $this->setsm_new_qty($newStockLevel);

            foreach($additionnal as $k => $v)
                $this->setData($k, $v);

            $this->save();
        }

        //manually update qty in warehouse having stock movements disabled
        if ($from && $this->hasStockMovementDisabled($from))
        {
            $wi = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $from);
            $wi->setwi_physical_quantity($wi->getwi_physical_quantity() - $qty)->save();
        }
        if ($to && $this->hasStockMovementDisabled($to))
        {
            $wi = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $to);
            $wi->setwi_physical_quantity($wi->getwi_physical_quantity() + $qty)->save();
        }

        return $this;
    }

    public function afterSave()
    {
        parent::afterSave();

        if ($this->_config->displayAdvancedLog()){
            $this->logAdjustment($this);
        }
    }

    protected function logAdjustment($sm)
    {
        try
        {
            throw new \Exception('');
        }
        catch(\Exception $ex)
        {
            $this->_stockMovementLogs
                ->setsm_id($sm->getId())
                ->setlog($ex->getTraceAsString())
                ->save();
        }
    }

    public function hasStockMovementDisabled($warehouseId)
    {
        if (self::$_disabledStockMovementWarehouseIds === null)
        {
            self::$_disabledStockMovementWarehouseIds = $this->_getResource()->getDisabledStockMovementWarehouseIds();
        }

        return (in_array($warehouseId, self::$_disabledStockMovementWarehouseIds));
    }
}

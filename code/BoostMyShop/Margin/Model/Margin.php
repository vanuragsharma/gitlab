<?php

namespace BoostMyShop\Margin\Model;

class Margin
{
    protected $_order;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory
    ){
        $this->_orderFactory = $orderFactory;
    }

    public function init($order)
    {
        if (is_numeric($order))
            $order = $this->_orderFactory->create()->load($order);
        $this->_order = $order;
    }

    public function getOrder()
    {
        return $this->_order;
    }

    public function getItems()
    {
        return $this->_order->getAllVisibleItems();
    }

    public function getOrderItemCost($orderItem)
    {
        switch($orderItem->getproduct_type())
        {
            case 'container':
            case 'bundle':
            case 'configurable':
                return $this->getTotalCostFromChildren($orderItem);
                break;
            default:
                return $orderItem->getbase_cost() * $orderItem->getqty_ordered();
        }
    }

    public function getMarginValue($orderItem)
    {
        return $this->getRowTotalExclTax($orderItem) - $this->getOrderItemCost($orderItem);
    }

    public function getMarginPercent($orderItem)
    {
        if ($this->getRowTotalExclTax($orderItem) > 0)
            return (int)($this->getMarginValue($orderItem) / ($this->getRowTotalExclTax($orderItem)) * 100);
        else
            return (int)$this->getMarginValue($orderItem);

    }

    public function getRowTotalExclTax($orderItem)
    {
        if ($orderItem->getbase_row_total() < $orderItem->getbase_row_total_incl_tax())
            return $orderItem->getbase_row_total();
        else
            return $orderItem->getbase_row_total() - $orderItem->getbase_tax_amount();
    }

    public function getOrderMarginValue()
    {
        $value = 0;
        foreach($this->getItems() as $orderItem)
            $value += $this->getMarginValue($orderItem);
        return $value;
    }

    public function getOrderMarginPercent()
    {
        if ($this->getOrderTotalExclTax() > 0)
            return (int)($this->getOrderMarginValue() / $this->getOrderTotalExclTax() * 100);
        else
            return (int)$this->getOrderMarginValue();
    }

    protected function getTotalCostFromChildren($parentOrderItem)
    {
        $totalCost = 0;

        foreach($this->_order->getAllItems() as $orderItem)
        {
            if ($orderItem->getparent_item_id() == $parentOrderItem->getId())
                $totalCost += $this->getOrderItemCost($orderItem);
        }

        return $totalCost;
    }

    public function getOrderTotalExclTax()
    {
        $totalCost = 0;

        foreach($this->_order->getAllItems() as $orderItem)
        {
			//consider only items without parent
            if (!$orderItem->getparent_item_id())
                $totalCost += $this->getRowTotalExclTax($orderItem);
        }

        return $totalCost;
    }

    public function getOrderTotalCosts()
    {
        $totalCost = 0;

        foreach($this->_order->getAllItems() as $orderItem)
        {
            if (!$orderItem->getparent_item_id())
                $totalCost += $this->getOrderItemCost($orderItem);
        }

        return $totalCost;
    }
}
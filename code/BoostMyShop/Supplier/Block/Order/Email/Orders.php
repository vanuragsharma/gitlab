<?php
namespace BoostMyShop\Supplier\Block\Order\Email;

class Orders extends \Magento\Framework\View\Element\Template
{
    public function getPurchaseOrders()
    {
        return $this->getData('orders');
    }

}

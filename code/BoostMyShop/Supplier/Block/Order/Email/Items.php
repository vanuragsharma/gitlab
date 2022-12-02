<?php

namespace BoostMyShop\Supplier\Block\Order\Email;

class Items extends \Magento\Framework\View\Element\Template
{
    public function getPurchaseOrder()
    {
    	$order = $this->getData('order');
    	$this->_eventManager->dispatch('purchase_order_email_item_render', ['block' => $this, 'order' => $order]);
        return $this->getData('order');
    }

}

<?php

namespace BoostMyShop\Supplier\Model\Order\Notification;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{

    public function setFromByStoreId($from, $storeId)
    {
        $result = $this->_senderResolver->resolve($from, $storeId);
        $this->message->setFrom($result['email'], $result['name']);
        return $this;
    }


}
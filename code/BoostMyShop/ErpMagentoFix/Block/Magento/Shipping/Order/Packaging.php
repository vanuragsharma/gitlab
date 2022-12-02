<?php

namespace BoostMyShop\ErpMagentoFix\Block\Magento\Shipping\Order;

class Packaging extends \Magento\Shipping\Block\Adminhtml\Order\Packaging
{

    public function getPackages()
    {
        foreach($this->getShipment()->getPackages() as $packageId => $package)
        {
            $package = new \Magento\Framework\DataObject($package);
            if($package->getParams() == null){
                return [];
            }
        }
        return $this->getShipment()->getPackages();
    }

}
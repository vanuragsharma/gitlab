<?php
namespace BoostMyShop\Supplier\Model;
class Registry
{
    protected $_adminSession;
    public function __construct(
        \Magento\Backend\Model\Auth\Session $adminSession
    ) {
        $this->_adminSession = $adminSession;
    }

    public function getCurrentWarehouseId()
    {
        return $this->_adminSession->getRwh();

    }
    public function setCurrentWarehouseId($id)
    {
        $this->_adminSession->setRwh($id);
        return $this;
    }


}
<?php namespace BoostMyShop\Supplier\Cron;

class MassNotification {

    protected $_massNotification;

    public function __construct(
        \BoostMyShop\Supplier\Model\Order\MassNotification $massNotification
    )
    {
        $this->_massNotification = $massNotification;
    }

    public function execute(){
        $this->_massNotification->processPendingNotifications();
    }

}
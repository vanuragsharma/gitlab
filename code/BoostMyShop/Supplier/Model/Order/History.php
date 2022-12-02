<?php

namespace BoostMyShop\Supplier\Model\Order;


/**
 * Class History
 * @package BoostMyShop\Supplier\Model\Order
 */
class History extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \BoostMyShop\Supplier\Model\Order
     */
    protected $_orderFactory;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\ResourceModel\Order\History');
    }

    /**
     * History constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \BoostMyShop\Supplier\Model\Order $orderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \BoostMyShop\Supplier\Model\Order $orderFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_date = $date;
        $this->_orderFactory = $orderFactory;
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $this->setpoh_date($this->_date->gmtDate());
        return $this;
    }

    /**
     * @param $userName
     * @param $description
     * @param $order
     */
    public function init($userName, $description, $order)
    {
        $this->setpoh_po_id($order->getId());
        $this->setpoh_username($userName);
        $this->setpoh_description($description);
    }
}

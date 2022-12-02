<?php

namespace BoostMyShop\OrderPreparation\Model\InProgress;


class Item extends \Magento\Framework\Model\AbstractModel
{
    protected $_orderItem = null;
    protected $_parentItem = null;
    protected $_orderItemFactory;
    protected $_eventManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $data = []
    )
    {
        $this->_orderItemFactory = $orderItemFactory;
        $this->_eventManager = $eventManager;

        parent::__construct($context, $registry, null, null, $data);

    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\Item');
    }

    public function getOrderItem()
    {
        if ($this->_orderItem == null)
        {
            $this->_orderItem = $this->_orderItemFactory->create()->load($this->getipi_order_item_id());
        }
        return $this->_orderItem;
    }

    public function getParentItem()
    {
        if ($this->_parentItem == null)
        {
            $this->_parentItem = $this->_orderItemFactory->create()->load($this->getOrderItem()->getparent_item_id());
        }
        return $this->_parentItem;
    }

    /**
     * @param $orderInProgressItem
     */
    public function getDatasForExport()
    {
        $datas = [];

        foreach($this->getData() as $k => $v)
        {
            if ((!is_array($v)) && (!is_object($v)))
                $datas['preparation.'.$k] = $v;
        }

        foreach($this->getOrderItem()->getData() as $k => $v)
        {
            if ((!is_array($v)) && (!is_object($v)))
                $datas['orderitem.'.$k] = $v;
        }

        if ($this->getOrderItem()->getProduct()) {
            foreach ($this->getOrderItem()->getProduct()->getData() as $k => $v) {
                if ((!is_array($v)) && (!is_object($v)))
                    $datas['product.' . $k] = $v;
            }
        }


        //raise event to allow other modules to inject new fields
        $obj = new \Magento\Framework\DataObject();
        $obj->setDatas($datas);
        $this->_eventManager->dispatch('bms_orderpreparation_inprogress_item_get_data_for_export', ['datas' => $obj, 'item' => $this]);
        $datas = $obj->getDatas();

        return $datas;
    }

    public function loadByOrderItemId($orderItemId)
    {
        $id = $this->_getResource()->getIdFromOrderItemId($orderItemId);
        return $this->load($id);
    }

    public function shipWithParent()
    {
        if ($this->getOrderItem()->getparent_item_id())
        {
            $parentItem = $this->getParentItem();
            if ($parentItem)
                return true;

        }

        return false;
    }
}
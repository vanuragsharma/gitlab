<?php

namespace BoostMyShop\AdvancedStock\Block\ErpProduct\Edit\Tab\OrdersToShip;

use Magento\Backend\Block\Widget\Grid\Column;

class StockAllocation extends \Magento\Backend\Block\Template
{
    protected $_template = 'ErpProduct/Edit/Tab/OrdersToShip/StockAllocation.phtml';

    protected $_coreRegistry;
    protected $_extendedItemsCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\ExtendedSalesFlatOrderItem\CollectionFactory $extendedItemsCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $coreRegistry;
        $this->_extendedItemsCollectionFactory = $extendedItemsCollectionFactory;
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    //Return order items having at least 1 reserved
    public function getAllocationFromItems()
    {
        return $this->_extendedItemsCollectionFactory
                            ->create()
                            ->joinOrderItem()
                            ->joinOpenedOrder()
                            ->addProductFilter($this->getProduct()->getId())
                            ->addQtyToShipFilter()
                            ->addFieldToFilter('esfoi_qty_reserved', ['gt' => 0])
                            ->setOrder('increment_id');
    }

    //Return order items having at least 1 reservation missing
    public function getAllocationToItems()
    {
        return $this->_extendedItemsCollectionFactory
                            ->create()
                            ->joinOrderItem()
                            ->joinOpenedOrder()
                            ->addProductFilter($this->getProduct()->getId())
                            ->addQtyToShipFilter()
                            ->addNotFullyReservedFilter()
                            ->setOrder('increment_id');
    }

}
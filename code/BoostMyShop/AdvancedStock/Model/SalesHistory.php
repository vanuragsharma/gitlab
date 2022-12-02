<?php

namespace BoostMyShop\AdvancedStock\Model;


class SalesHistory extends \Magento\Framework\Model\AbstractModel
{
    protected $_warehouseItemCollection;
    protected $_config;
    protected $_productFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollection,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_warehouseItemCollection = $warehouseItemCollection;
        $this->_config = $config;
        $this->_productFactory = $productFactory;
    }

    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\SalesHistory');
    }

    public function updateForProduct($productId)
    {
        $product = $this->_productFactory->create()->load($productId);
        $websiteIds = $product->getWebsiteIds();
        $websiteId = reset($websiteIds);
        $collection = $this->_warehouseItemCollection->create()->addProductFilter($productId);
        foreach($collection as $item)
            $this->updateForProductWarehouse($item, $websiteId);
    }

    public function updateForProductWarehouse($warehouseItem, $websiteId)
    {
        if(!$websiteId)
            $websiteId = 0;
        $ranges = [];
        for ($i=1;$i<=3;$i++)
            $ranges[] = $this->_config->getSetting('stock_level/history_range_'.$i, $websiteId);
        $qtyToUse = $this->_config->getSetting('stock_level/history_qty_selection', $websiteId);

        $this->_getResource()->updateForProductWarehouse($warehouseItem, $ranges, $qtyToUse);

        return $this->load($warehouseItem->getId(), 'sh_warehouse_item_id');
    }

}
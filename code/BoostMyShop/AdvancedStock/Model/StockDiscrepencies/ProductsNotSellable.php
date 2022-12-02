<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class ProductsNotSellable extends AbstractDiscrepencies
{
    protected $_stockItemCollectionFactory;
    protected $_config;

    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Stock\Item\CollectionFactory $stockItemCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config
    )
    {
        parent::__construct($stockRegistry);

        $this->_stockItemCollectionFactory = $stockItemCollectionFactory;
        $this->_config = $config;
    }

    public function run(&$results, $fix, $productId = null)
    {
        $results['products_not_sellable'] = ['explanations' => 'Products with a quantity sellable but out of stock', 'items' => []];
        $defaultBackorderConfig = $this->_config->getMagentoBackorderSetting();

        $collection = $this->_stockItemCollectionFactory
                                    ->create()
                                    ->addFieldToFilter('is_in_stock', 0)
                                    ->addSimpleProductFilter()
                                    ->addQtyGreaterThanMinQtyFilter($defaultBackorderConfig);
        foreach($collection as $item)
        {
            $results['products_not_sellable']['items'][] = 'Product #'.$item['product_id'].',  Stock #'.$item['stock_id'].' : qty sellable '.(int)$item['qty'];
            if ($fix)
                $item->setis_in_stock(1)->save();
        }

        return $results;
    }

}

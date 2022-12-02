<?php namespace BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\Item;

/**
 * Class Collection
 *
 * @package   BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\Item
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\StockTake\Item', 'BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\Item');
    }

    public function addStockTakeFilter($stockTakeId)
    {
        $this->getSelect()->where('stai_stock_take_id = '.$stockTakeId);
        return $this;
    }

    public function addSkuFilter($sku)
    {
        $this->getSelect()->where('stai_sku = "'.$sku.'"');
        return $this;
    }

    public function addStockTakeModeConditionForApplication($stockTakeMode)
    {
        if ($stockTakeMode == 'partial')
        {
            $this->addFieldToFilter('stai_status', \BoostMyShop\AdvancedStock\Model\StockTake\Item::STATUS_DIFFERENT);
        }
        else
        {
            $this->getSelect()->where('stai_expected_qty <> stai_scanned_qty');
        }

        return $this;
    }

}
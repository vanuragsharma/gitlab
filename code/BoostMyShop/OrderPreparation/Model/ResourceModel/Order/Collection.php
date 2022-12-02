<?php

namespace BoostMyShop\OrderPreparation\Model\ResourceModel\Order;

use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
{

    public function addAdditionalFields()
    {
        $this->getSelect()->join(
                                ['so' => $this->getTable('sales_order')],
                                "so.entity_id = main_table.entity_id",
                                ['weight', 'shipping_method','total_item_count']);

        return $this;
    }

}
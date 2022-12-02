<?php

namespace BoostMyShop\AdvancedStock\Model\Config\Source;

class SalesHistoryQuantities implements \Magento\Framework\Option\ArrayInterface
{

    protected $_collectionFactory;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $options = array();

        $options[] = array('value' => 'qty_ordered', 'label' => __('Ordered qty'));
        $options[] = array('value' => 'qty_invoiced', 'label' => __('Invoiced qty'));
        $options[] = array('value' => 'qty_shipped', 'label' => __('Shipped qty'));

        return $options;
    }

    public function toArray()
    {

    }

}

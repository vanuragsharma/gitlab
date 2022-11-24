<?php
namespace Yalla\Vendors\Model\ResourceModel\VendorProducts;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'vp_id';
    protected $_eventPrefix = 'vendor_products_collection';
    protected $_eventObject = 'vendor_products_collection';
    
    protected function _construct()
    {
        $this->_init(
            'Yalla\Vendors\Model\VendorProducts',
            'Yalla\Vendors\Model\ResourceModel\VendorProducts'
        );
    }
}

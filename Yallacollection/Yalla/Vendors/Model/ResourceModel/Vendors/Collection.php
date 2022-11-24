<?php
namespace Yalla\Vendors\Model\ResourceModel\Vendors;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'vendor_id';
    protected $_eventPrefix = 'vendors_collection';
    protected $_eventObject = 'vendors_collection';
    
    protected function _construct()
    {
        $this->_init(
            'Yalla\Vendors\Model\Vendors',
            'Yalla\Vendors\Model\ResourceModel\Vendors'
        );
    }
}
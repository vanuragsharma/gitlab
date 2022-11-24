<?php

namespace Yalla\Vendors\Model;

class VendorProducts extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface {

    const CACHE_TAG = 'vendor_products';

    protected $_cacheTag = 'vendor_products';
    protected $_eventPrefix = 'vendor_products';

    protected function _construct()
    {
        $this->_init('Yalla\Vendors\Model\ResourceModel\VendorProducts');
    }
    
    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues() {
        $values = [];

        return $values;
    }

}

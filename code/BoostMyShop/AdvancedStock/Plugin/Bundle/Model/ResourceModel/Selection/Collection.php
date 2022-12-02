<?php

namespace BoostMyShop\AdvancedStock\Plugin\Bundle\Model\ResourceModel\Selection;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Framework\App\ObjectManager;

//Magento 2.2 compatibility
class Collection extends \Magento\Bundle\Model\ResourceModel\Selection\Collection
{

    /**
     * Add website filter
     *
     * @return $this
     * @since 100.2.0
     */
    public function addQuantityFilter()
    {
        parent::addQuantityFilter();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();

        $this->getSelect()
            ->where(
                'stock_item.website_id = '. $websiteId
            );

        return $this;
    }

}

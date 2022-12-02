<?php

namespace BoostMyShop\AdvancedStock\Block\MassStockEditor\Filter;

use Magento\Framework\DataObject;

class AdditionalBarcodes extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    protected $_eavConfig;
    protected $_eavAttribute;
    protected $_advancedStockConfig;
    protected $_productCollectionFactory;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \Magento\Framework\DB\Helper $resourceHelper,
                                \Magento\Eav\Model\Config $eavConfig,
                                \Magento\Eav\Model\Entity\Attribute $eavAttribute,
                                \BoostMyShop\AdvancedStock\Model\Config $advancedStockConfig,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                array $data = [])
    {
        parent::__construct($context, $resourceHelper, $data);
        $this->_eavConfig = $eavConfig;
        $this->_eavAttribute = $eavAttribute;
        $this->_advancedStockConfig = $advancedStockConfig;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    public function getCondition()
    {
        $productIds = [];
        $value = $this->getValue();
        if (!$value) {
            return null;
        }
        $collection = $this->getCollection()->getSelect()
                      ->where( 'attrp.value LIKE "%'.$value.'%" OR bab.bac_product_id like "%'.$value.'%"');

        $collection->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(new \Zend_Db_Expr('distinct e.entity_id'));
        $productIds = $collection->getConnection()->fetchCol($collection);

        return ['in' => $productIds];
    }
    public function getCollection()
    {
        $entityTypeId = $this->_eavConfig->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)->getEntityTypeId();
        $productAttribute = $this->_eavAttribute->loadByCode($entityTypeId, $this->getBarcodeAttribute());
        $collection = $this->_productCollectionFactory->create();
        $collection->getSelect()->joinLeft(
            ["attrp" => "catalog_product_entity_".$productAttribute->getBackendType()],
            "e.entity_id = attrp.entity_id AND
            attrp.attribute_id = ".$productAttribute->getAttributeId(). " AND
            attrp.store_id = ".\Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ["barcode"=>"attrp.value"]
        )->joinLeft(
            ["bab" => "bms_advancedstock_barcodes"],
            "e.entity_id = bab.bac_product_id",
            ["additional_barcode"=>"bab.bac_code"]
        );
        return $collection;
    }
    public function getBarcodeAttribute()
    {
        return $this->_advancedStockConfig->getBarcodeAttribute();
    }
}
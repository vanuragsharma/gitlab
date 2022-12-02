<?php

namespace BoostMyShop\AdvancedStock\Setup;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;


class UpgradeData implements UpgradeDataInterface
{

    protected $eavSetupFactory;
    protected $_websiteCollectionFactory;
    protected $_stockCollectionFactory;
    protected $_stockFactory;
    protected $_productCollectionFactory;
    protected $_productAction;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\CollectionFactory $stockCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\CatalogInventory\Model\StockFactory $stockFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction
    ) {
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_stockCollectionFactory = $stockCollectionFactory;
        $this->_stockFactory = $stockCollectionFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productAction = $productAction;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        //insert cataloginventory_stock for websites
        if (version_compare($context->getVersion(), '0.0.13') < 0)
        {
            $select = $setup->getConnection()->select()->from($setup->getTable('cataloginventory_stock'), ['website_id']);
            $existingWebsiteIds = $setup->getConnection()->fetchCol($select);

            $select = $setup->getConnection()->select()->from($setup->getTable('store_website'), ['website_id']);
            $allWebsiteIds = $setup->getConnection()->fetchCol($select);

            $missingWebsiteIds = array_diff($allWebsiteIds, $existingWebsiteIds);

            foreach($missingWebsiteIds as $websiteId)
            {
                $sql = 'insert into '.$setup->getTable('cataloginventory_stock').' (website_id, stock_name) values ('.$websiteId.', "For website #'.$websiteId.'")';
                $setup->getConnection()->query($sql);
            }

        }

        //insert cataloginventory_stock_items for websites
        if (version_compare($context->getVersion(), '0.0.14') < 0)
        {
            $sql = 'insert ignore
                    into '.$setup->getTable('cataloginventory_stock_item').'
                    (product_id, stock_id, qty, min_qty, use_config_min_qty, is_in_stock, backorders, use_config_backorders, website_id, min_sale_qty, use_config_min_sale_qty, max_sale_qty, use_config_max_sale_qty, notify_stock_qty, use_config_notify_stock_qty, manage_stock, use_config_manage_stock, stock_status_changed_auto, qty_increments, use_config_enable_qty_inc)
                    select
                        product_id, cs.stock_id, qty, min_qty, use_config_min_qty, is_in_stock, backorders, use_config_backorders, cs.website_id, min_sale_qty, use_config_min_sale_qty, max_sale_qty, use_config_max_sale_qty, notify_stock_qty, use_config_notify_stock_qty, manage_stock, use_config_manage_stock, stock_status_changed_auto, qty_increments, use_config_enable_qty_inc
                    from
                        '.$setup->getTable('cataloginventory_stock_item').' csi
                        join '.$setup->getTable('cataloginventory_stock').' cs
                    where
                        csi.stock_id = 1
                        and cs.stock_id > 1
                    ';
            $setup->getConnection()->query($sql);
        }

        if (version_compare($context->getVersion(), '0.0.26') < 0)
        {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'disable_lowstock_update',
                [
                    'group' => 'General',
                    'type' => 'int',
                    'input' => 'boolean',
                    'label' => 'Disable lowstock update',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'default' => 0,
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.29') < 0) {
            //init default value for supply_discontinued
            $productIds = $this->_productCollectionFactory->create()->getAllIds();
            $arrays = array_chunk($productIds, 200);
            foreach ($arrays as $array) {
                $this->_productAction->updateAttributes($array, ['disable_lowstock_update' => 0], 0);
            }
        }

        $setup->endSetup();
    }

}

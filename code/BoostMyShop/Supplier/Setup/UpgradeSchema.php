<?php

namespace BoostMyShop\Supplier\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.4', '<')) {

            $setup->getConnection()->changeColumn(
                $setup->getTable('bms_purchase_order_product'),
                'pop_sku',
                'pop_sku',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255
                ]
            );

            $setup->getConnection()->changeColumn(
                $setup->getTable('bms_purchase_order_product'),
                'pop_supplier_sku',
                'pop_supplier_sku',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255
                ]
            );

        }

        if (version_compare($context->getVersion(), '0.0.5', '<')) {

            $setup->getConnection()->changeColumn($setup->getTable('bms_supplier'),
                'sup_minimum_of_order',
                'sup_minimum_of_order',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_supplier'),
                'sup_carriage_free_amount',
                'sup_carriage_free_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );

            $setup->getConnection()->changeColumn($setup->getTable('bms_supplier_product'),
                'sp_price',
                'sp_price',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_supplier_product'),
                'sp_base_price',
                'sp_base_price',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );

            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order'),
                'po_shipping_cost',
                'po_shipping_cost',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order'),
                'po_shipping_cost_base',
                'po_shipping_cost_base',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order'),
                'po_additionnal_cost',
                'po_additionnal_cost',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order'),
                'po_additionnal_cost_base',
                'po_additionnal_cost_base',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order'),
                'po_tax',
                'po_tax',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order'),
                'po_tax_base',
                'po_tax_base',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order'),
                'po_subtotal',
                'po_subtotal',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order'),
                'po_subtotal_base',
                'po_subtotal_base',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order'),
                'po_grandtotal',
                'po_grandtotal',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order'),
                'po_grandtotal_base',
                'po_grandtotal_base',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );

            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order_product'),
                'pop_price',
                'pop_price',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order_product'),
                'pop_price_base',
                'pop_price_base',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order_product'),
                'pop_tax',
                'pop_tax',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order_product'),
                'pop_tax_base',
                'pop_tax_base',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order_product'),
                'pop_subtotal',
                'pop_subtotal',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order_product'),
                'pop_subtotal_base',
                'pop_subtotal_base',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order_product'),
                'pop_grandtotal',
                'pop_grandtotal',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
            $setup->getConnection()->changeColumn($setup->getTable('bms_purchase_order_product'),
                'pop_grandtotal_base',
                'pop_grandtotal_base',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4'
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.6', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order'),
                'po_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 20,
                    'nullable' => true,
                    'comment' => 'PO Type',
                    'default' => 'po'
                ]
            );


        }

        if (version_compare($context->getVersion(), '0.0.7', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_payment_terms',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 30,
                    'nullable' => true,
                    'comment' => 'Supplier payment terms'
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.8', '<')) {
            $setup->getConnection()->addIndex(
                $setup->getTable('bms_supplier_product'),
                $setup->getIdxName('bms_supplier_product', 'sp_product_id', 'sp_sup_id'),
                ['sp_product_id', 'sp_sup_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );
        }

        if (version_compare($context->getVersion(), '0.0.9', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier_product'),
                'sp_primary',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Primary supplier'
                ]
            );

        }

        if (version_compare($context->getVersion(), '0.0.10', '<')) {
            $setup->getConnection()->addIndex(
                $setup->getTable('bms_purchase_order_product'),
                $setup->getIdxName('bms_purchase_order_product', 'pop_po_id'),
                ['pop_po_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );
            $setup->getConnection()->addIndex(
                $setup->getTable('bms_purchase_order_product'),
                $setup->getIdxName('bms_purchase_order_product', 'pop_product_id'),
                ['pop_product_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );
            $setup->getConnection()->addIndex(
                $setup->getTable('bms_purchase_order_reception'),
                $setup->getIdxName('bms_purchase_order_reception', 'por_po_id'),
                ['por_po_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );
            $setup->getConnection()->addIndex(
                $setup->getTable('bms_purchase_order_reception_item'),
                $setup->getIdxName('bms_purchase_order_reception_item', 'pori_por_id'),
                ['pori_por_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );
            $setup->getConnection()->addIndex(
                $setup->getTable('bms_purchase_order_reception_item'),
                $setup->getIdxName('bms_purchase_order_reception_item', 'pori_product_id'),
                ['pori_product_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );
            $setup->getConnection()->addIndex(
                $setup->getTable('bms_supplier_product'),
                $setup->getIdxName('bms_supplier_product', 'sp_product_id'),
                ['sp_product_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );
            $setup->getConnection()->addIndex(
                $setup->getTable('bms_supplier_product'),
                $setup->getIdxName('bms_supplier_product', 'sp_sup_id'),
                ['sp_sup_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );
        }

        if (version_compare($context->getVersion(), '0.0.11', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order'),
                'po_shipping_method',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'PO Shipping Method',
                    'default' => ''
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order'),
                'po_shipping_tracking',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => true,
                    'comment' => 'PO Tracking number',
                    'default' => ''
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.12', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order'),
                'po_invoice_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 20,
                    'nullable' => true,
                    'comment' => 'PO invoice status',
                    'default' => 'undefined'
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.13', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order_product'),
                'pop_extended_cost',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => true,
                    'comment' => 'Extended costs',
                    'default' => 0
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order_product'),
                'pop_extended_cost_base',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => true,
                    'comment' => 'Extended costs base',
                    'default' => 0
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.14', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order_product'),
                'pop_change_rate',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => true,
                    'comment' => 'Change rate',
                    'default' => 1
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.15', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order_product'),
                'pop_eta',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => true,
                    'comment' => 'Eta'
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.18', '<')) {

            /**
             * Create table 'bms_supplier_invoice'
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_supplier_invoice'))
                ->addColumn('bsi_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Supplier Invoice id')
                ->addColumn('bsi_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10, [], 'Type')
                ->addColumn('bsi_sup_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Supplier id')
                ->addColumn('bsi_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created at')
                ->addColumn('bsi_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Invoice date')
                ->addColumn('bsi_due_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Due date')
                ->addColumn('bsi_reference', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 200, ['unsigned' => true, 'nullable' => false], 'Reference')
                ->addColumn('bsi_total', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,2', [], 'Total')
                ->addColumn('bsi_total_paid', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,2', [], 'Total paid')
                ->addColumn('bsi_total_applied', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,2', [], 'Total applied')
                ->addColumn('bsi_attachment_filename', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 200, [], 'filename')
                ->addColumn('bsi_status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Status')
                ->setComment('Supplier Invoice');
            $setup->getConnection()->createTable($table);

            /**
             * Create table 'bms_supplier_invoice_order'
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_supplier_invoice_order'))
                ->addColumn('bsio_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Invoice Order id')
                ->addColumn('bsio_invoice_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Invoice id')
                ->addColumn('bsio_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Order id')
                ->addColumn('bsio_total', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,2', [], 'Total')
                ->setComment('Supplier Invoice Order');
            $setup->getConnection()->createTable($table);

            /**
             * Create table 'bms_supplier_invoice_payments'
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_supplier_invoice_payments'))
                ->addColumn('bsip_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Invoice Payment id')
                ->addColumn('bsip_invoice_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Invoice id')
                ->addColumn('bsip_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Payment date')
                ->addColumn('bsip_method', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Method')
                ->addColumn('bsip_total', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,2', [], 'Total')
                ->addColumn('bsip_notes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'Notes')
                ->setComment('Supplier Invoice Payments');
            $setup->getConnection()->createTable($table);


            $setup->getConnection()->addIndex(
                $setup->getTable('bms_supplier_invoice_order'),
                $setup->getIdxName('bms_supplier_invoice_order', 'bsio_invoice_id'),
                ['bsio_invoice_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );

            $setup->getConnection()->addIndex(
                $setup->getTable('bms_supplier_invoice_order'),
                $setup->getIdxName('bms_supplier_invoice_order', 'bsio_order_id'),
                ['bsio_order_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );

            $setup->getConnection()->addIndex(
                $setup->getTable('bms_supplier_invoice_payments'),
                $setup->getIdxName('bms_supplier_invoice_payments', 'bsip_invoice_id'),
                ['bsip_invoice_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier_invoice'),
                'bsi_notes',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '64k',
                    'nullable' => true,
                    'comment' => 'Notes',
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.16', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier_product'),
                'sp_notes',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'supplier notes',
                    'default' => ''
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier_product'),
                'sp_moq',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'comment' => 'supplier moq',
                    'nullable' => true,
                    'default' => null
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier_product'),
                'sp_shipping_delay',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'comment' => 'supplier shipping_delay',
                    'nullable' => true,
                    'default' => null
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier_product'),
                'sp_supply_delay',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'supplier supply_delay',
                    'default' => null
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_supply_delay',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'supplier supply_delay',
                    'default' => null
                ]
            );

        }

        if (version_compare($context->getVersion(), '0.0.19', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier_product'),
                'sp_pack_qty',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => false,
                    'comment' => 'supplier pack_qty',
                    'default' => 1
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order_product'),
                'pop_qty_pack',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => false,
                    'comment' => 'qty_pack',
                    'default' => 1
                ]
            );

        }

        if (version_compare($context->getVersion(), '0.0.20', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order_product'),
                'pop_discount_percent',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '4,2',
                    'nullable' => false,
                    'comment' => 'discount percent',
                    'default' => 0
                ]
            );

        }

        if (version_compare($context->getVersion(), '0.0.21', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order'),
                'po_missing_price',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 1,
                    'nullable' => false,
                    'comment' => 'Has missing prices',
                    'default' => 0
                ]
            );

        }

        if (version_compare($context->getVersion(), '0.0.23', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier_product'),
                'sp_last_buying_price',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '10,4',
                    'nullable' => true,
                    'comment' => 'last buying price with supplier currency'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier_product'),
                'sp_last_buying_price_base',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '10,4',
                    'nullable' => true,
                    'comment' => 'last buying price with base currency'
                ]
            );

        }

        if (version_compare($context->getVersion(), '0.0.26', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order_reception_item'),
                'pori_qty_pack',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 1,
                    'nullable' => false,
                    'comment' => 'Qty pack',
                    'default' => 1
                ]
            );

        }

        if (version_compare($context->getVersion(), '0.0.27', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_global_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '4,2',
                    'nullable' => true,
                    'comment' => 'global discount',
                    'default' => 0
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order'),
                'po_global_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '4,2',
                    'nullable' => false,
                    'comment' => 'Global discount',
                    'default' => 0
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.28', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order'),
                'po_verified',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 1,
                    'nullable' => false,
                    'comment' => 'Verified',
                    'default' => 0
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.29', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order'),
                'po_website_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 1,
                    'nullable' => true,
                    'comment' => 'Website id'
                ]
            );


            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_website_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 1,
                    'nullable' => true,
                    'comment' => 'Website id'
                ]
            );

        }


        if (version_compare($context->getVersion(), '0.0.30', '<')) {


            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_enable_notification',
                [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length'    => 1,
                    'nullable'  => true,
                    'default'   => 1,
                    'comment' => 'Enable email notification'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_attach_pdf',
                [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length'    => 1,
                    'nullable'  => true,
                    'default'   => 1,
                    'comment' => 'Attach PDF'
                ]
            );


            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_attach_file',
                [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length'    => 1,
                    'nullable'  => true,
                    'default'   => 0,
                    'comment' => 'Attach file'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_file_name',
                [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'    => 200,
                    'nullable'  => true,
                    'comment' => 'file name'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_file_header',
                [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'    => 800,
                    'nullable'  => true,
                    'comment' => 'file header'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_file_order_header',
                [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'    => 800,
                    'nullable'  => true,
                    'comment' => 'file order header'
                ]
            );


            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_file_product',
                [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'    => 800,
                    'nullable'  => true,
                    'comment' => 'fil product'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_file_order_footer',
                [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'    => 800,
                    'nullable'  => true,
                    'comment' => 'file order footer'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_file_footer',
                [
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'    => 800,
                    'nullable'  => true,
                    'comment' => 'file footer'
                ]
            );

        }

        if (version_compare($context->getVersion(), '0.0.33', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order'),
                'po_shipping_label_path',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Shipping label pdf file path'
                ]
            );

        }
        if (version_compare($context->getVersion(), '0.0.35', '<')) {

            /**
             * Create table 'bms_purchase_order_history'
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_purchase_order_history'))
                ->addColumn('poh_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'purchase order history id')
                ->addColumn('poh_po_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'purchase order  id')
                ->addColumn('poh_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'po history date')
                ->addColumn('poh_username', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'username')
                ->addColumn('poh_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'po history description')
                ->setComment('purchase order history');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()->addIndex(
                $setup->getTable('bms_purchase_order_history'),
                $setup->getIdxName('bms_purchase_order_history', 'poh_po_id'),
                ['poh_po_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );

        }

        if (version_compare($context->getVersion(), '0.0.34', '<')) {

            $setup->getConnection()->changeColumn(
                $setup->getTable('bms_supplier'),
                'sup_code',
                'sup_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.36', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_delayed_notification',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default'   => 0,
                    'comment' => 'Supplier delayed notification'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_delayed_notification_hours',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default'   => null,
                    'comment' => 'Supplier delayed notification hours'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order'),
                'po_delayed_notified',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default'   => 0,
                    'comment' => 'Delayed notified'
                ]
            );

            $setup->getConnection()->addIndex(
                $setup->getTable('bms_purchase_order'),
                $setup->getIdxName('bms_purchase_order', 'po_delayed_notified'),
                ['po_delayed_notified'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );
        }


        if (version_compare($context->getVersion(), '0.0.37', '<')) {

            $setup->getConnection()->addIndex(
                $setup->getTable('bms_purchase_order'),
                $setup->getIdxName('bms_purchase_order', 'po_status'),
                ['po_status'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );

            $setup->getConnection()->addIndex(
                $setup->getTable('bms_purchase_order'),
                $setup->getIdxName('bms_purchase_order', 'po_delayed_notified'),
                ['po_delayed_notified'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );

            $setup->getConnection()->addIndex(
                $setup->getTable('bms_supplier'),
                $setup->getIdxName('bms_supplier', 'sup_delayed_notification'),
                ['sup_delayed_notification'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );

        }

        if (version_compare($context->getVersion(), '0.0.38', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier_product'),
                'sp_discontinued',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Discontinued'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier_product'),
                'sp_availability_date',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    'nullable' => true,
                    'comment' => 'Availability date'
                ]
            );

        }

        if (version_compare($context->getVersion(), '0.0.39', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_purchase_order'),
                'po_external_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'PO External Id'
                ]
            );
            $setup->getConnection()->addIndex(
                $setup->getTable('bms_purchase_order'),
                $setup->getIdxName('bms_purchase_order', 'po_external_id'),
                ['po_external_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );

        }

        if (version_compare($context->getVersion(), '0.0.40', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_po_notified_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 30,
                    'nullable' => true,
                    'default'   => null,
                    'comment' => 'Status to assign to PO when notified'
                ]
            );

        }

        if (version_compare($context->getVersion(), '0.0.41', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_notif_ftp_enabled',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default'   => 0,
                    'comment' => 'supplier notification ftp enable'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_notif_ftp_sftp',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default'   => 0,
                    'comment' => 'supplier notification ftp sftp'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_notif_ftp_passive',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default'   => 0,
                    'comment' => 'supplier notification ftp passive'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_notif_ftp_host',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 200,
                    'nullable' => true,
                    'comment' => 'supplier notification ftp host'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_notif_ftp_port',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 200,
                    'nullable' => true,
                    'comment' => 'supplier notification ftp port'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_notif_ftp_login',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 200,
                    'nullable' => true,
                    'comment' => 'supplier notification ftp login'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_notif_ftp_password',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 200,
                    'nullable' => true,
                    'comment' => 'supplier notification ftp password'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_notif_ftp_directory',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 200,
                    'nullable' => true,
                    'comment' => 'supplier notification ftp directory'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_supplier'),
                'sup_notif_ftp_file_name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 200,
                    'nullable' => true,
                    'comment' => 'supplier notification ftp file name'
                ]
            );

        }

        $setup->endSetup();
    }



}

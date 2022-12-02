<?php

namespace BoostMyShop\OrderPreparation\Setup;

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

        //0.0.3
        if (version_compare($context->getVersion(), '0.0.3', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_invoice_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Invoice ID'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_shipment_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Shipment ID'
                ]
            );

        }

        //0.0.4
        if (version_compare($context->getVersion(), '0.0.4', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_weights',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'size'  => 255,
                    'nullable' => true,
                    'comment' => 'Parcel weights'
                ]
            );
        }


        //0.0.5
        if (version_compare($context->getVersion(), '0.0.5', '<')) {

            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_orderpreparation_carrier_template'))
                ->addColumn('ct_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'id')
                ->addColumn('ct_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Name')
                ->addColumn('ct_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created at')
                ->addColumn('ct_shipping_methods', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 500, [], 'Associated shipping methods')
                ->addColumn('ct_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [], 'Template type')
                ->addColumn('ct_disabled', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [], 'Disabled ?')
                ->addColumn('ct_export_file_mime', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Mime type')
                ->addColumn('ct_export_file_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Export file name')
                ->addColumn('ct_export_file_header', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2000, [], 'Export file header')
                ->addColumn('ct_export_file_order_header', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2000, [], 'Export file order header')
                ->addColumn('ct_export_file_order_products', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2000, [], 'Export file order products')
                ->addColumn('ct_export_file_order_footer', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2000, [], 'Export file order footer')
                ->addColumn('ct_import_file_separator', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 5, [], 'Import file separator')
                ->addColumn('ct_import_file_skip_first_line', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 5, [], 'Import file skip first line')
                ->addColumn('ct_import_file_shipment_reference_index', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 5, [], 'Import file shipment reference index')
                ->addColumn('ct_import_file_tracking_index', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 5, [], 'Import file tracking index')
                ->setComment('Carrier templates');
            $setup->getConnection()->createTable($table);
        }

        //0.0.8
        if (version_compare($context->getVersion(), '0.0.8', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_warehouse_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Warehouse ID'
                ]
            );
        }

        //0.0.9
        if (version_compare($context->getVersion(), '0.0.9', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress_item'),
                'ipi_parent_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'In progress ID'
                ]
            );
        }

        //0.0.10
        if (version_compare($context->getVersion(), '0.0.10', '<')) {

            $setup->getConnection()->truncateTable($setup->getTable('bms_orderpreparation_inprogress'));
            $setup->getConnection()->truncateTable($setup->getTable('bms_orderpreparation_inprogress_item'));
        }

        //0.0.11
        if (version_compare($context->getVersion(), '0.0.11', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_export_file_footer',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'size'  => 2000,
                    'nullable' => true,
                    'comment' => 'Export file footer',
                ]
            );
        }


        //0.0.12
        if (version_compare($context->getVersion(), '0.0.12', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_total_weight',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '6,2',
                    'comment' => 'Total weight'
                ]
            );
        }

        //0.0.13
        if (version_compare($context->getVersion(), '0.0.13', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_total_volume',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '10,2',
                    'comment' => 'Total volume'
                ]
            );
        }


        //0.0.14
        if (version_compare($context->getVersion(), '0.0.14', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_website_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Website ID',
                ]
            );

        }


        //0.0.15
        if (version_compare($context->getVersion(), '0.0.15', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_export_order_filter',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Export order filter',
                ]
            );

        }

        //0.0.16
        if (version_compare($context->getVersion(), '0.0.16', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_import_create_shipment',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Create shipment on tracking import',
                ]
            );

        }

        //0.0.17
        if (version_compare($context->getVersion(), '0.0.17', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_import_file_order_reference_index',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Order reference index',
                ]
            );

        }

        //0.0.18
        if (version_compare($context->getVersion(), '0.0.18', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_shipment'),
                'packer_user_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'User id',
                ]
            );

            $setup->getConnection()->addIndex(
                $setup->getTable('sales_shipment'),
                $setup->getIdxName('sales_shipment', ['packer_user_id']),
                ['packer_user_id']
            );

        }

        //0.0.19
        if (version_compare($context->getVersion(), '0.0.19', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_parcel_count',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => 1,
                    'comment' => 'Parcel count'
                ]
            );

        }

        //0.0.20
        if (version_compare($context->getVersion(), '0.0.20', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_export_remove_accents',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Remove accents',
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.21', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_custom_data',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'size'  => 100,
                    'nullable' => true,
                    'comment' => 'UPS tracking data',
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.22', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_export_line_break',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'size'  => 50,
                    'comment' => 'Line break'
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.23', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_height',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Parcel height',
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_width',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Parcel width',
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_length',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Parcel length',
                ]
            );
        }
        //0.0.24
        if (version_compare($context->getVersion(), '0.0.24', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_boxes',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'size'  => 50,
                    'comment' => 'ip boxes',
                ]
            );
        }

        //0.0.25
        if (version_compare($context->getVersion(), '0.0.25', '<')) {

            $tableName = $setup->getTable('bms_orderpreparation_batch');
            if ($setup->getConnection()->isTableExists($tableName) != true) {
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'bob_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'id'
                    )
                    ->addColumn('bob_warehouse_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'warehouse id')
                    ->addColumn('bob_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [], 'Created at')
                    ->addColumn('bob_order_count', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Batch order count')
                    ->addColumn('bob_product_count', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Batch product count')
                    ->addColumn('bob_progress', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Progress')
                    ->addColumn('bob_label', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Batch label')
                    ->addColumn('bob_carrier', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Batch carrier')
                    ->addColumn('bob_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10, [], 'Batch type')
                    ->addColumn('bob_status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [], 'Batch status')
                    ->setComment('bms_orderpreparation_batch Table')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8')
                    ->addIndex(
                        $setup->getIdxName('bms_orderpreparation_batch', ['bob_status']),
                        ['bob_status']
                    )
                    ->addIndex(
                        $setup->getIdxName('bms_orderpreparation_batch', ['bob_warehouse_id']),
                        ['bob_warehouse_id']
                    );

                $setup->getConnection()->createTable($table);

                $setup->getConnection()->addColumn(
                    $setup->getTable('bms_orderpreparation_inprogress'),
                    'ip_batch_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        'nullable' => true,
                        'comment' => 'Batch id'
                    ]
                );

                $setup->getConnection()
                    ->addIndex(
                        $setup->getTable('bms_orderpreparation_inprogress'),
                        $setup->getIdxName('bms_orderpreparation_inprogress', ['ip_batch_id']),
                        'ip_batch_id'
                    );
            }
        }

        //0.0.26
        if (version_compare($context->getVersion(), '0.0.26', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_warehouse_ids',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'size'  => 50,
                    'comment' => 'warehouse ids',
                ]
            );
        }

        //0.0.27
        if (version_compare($context->getVersion(), '0.0.27', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_custom',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'size'  => 500,
                    'comment' => 'custom fields',
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.28', '<')) {

            $table = $setup->getConnection()
                ->newTable($setup->getTable('bms_orderpreparation_manifest'))
                ->addColumn('bom_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'id')
                ->addColumn('bom_date', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [], 'date')
                ->addColumn('bom_warehouse_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'warehouse id')
                ->addColumn('bom_carrier', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [], 'Manifest carrier')
                ->addColumn('bom_shipment_count', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Manifest order count')
                ->addColumn('bom_edi_status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 0, [], 'Manifest status');

            $setup->getConnection()->createTable($table);

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_shipment'),
                'manifest_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    'nullable' => true,
                    'comment' => 'Manifest id'
                ]
            );

            $setup->getConnection()->addIndex(
                $setup->getTable('sales_shipment'),
                $setup->getIdxName('sales_shipment', ['manifest_id']),
                'manifest_id'
            );
        }

        //0.0.29
        if (version_compare($context->getVersion(), '0.0.29', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_manifest_freetext',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'size'  => 500,
                    'comment' => 'manifest freetext field',
                ]
            );
        }

        //0.0.30
        if (version_compare($context->getVersion(), '0.0.30', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_shipping_label_pregenerated_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'size'  => 5,
                    'default' => 0,
                    'comment' => 'Shipping Label Pregenerated_Status',
                ]
            );
            $setup->getConnection()
                ->addIndex(
                    $setup->getTable('bms_orderpreparation_inprogress'),
                    $setup->getIdxName('bms_orderpreparation_inprogress', ['ip_shipping_label_pregenerated_status']),
                    'ip_shipping_label_pregenerated_status'
                );

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_shipping_label_pregenerated_tracking',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'size'  => 255,
                    'comment' => 'Shipping Label Pregenerated Tracking',
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_shipping_label_pregenerated_label_path',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'size'  => 255,
                    'comment' => 'Shipping Label Pregenerated Path',
                ]
            );
        }

        //0.0.31
        if (version_compare($context->getVersion(), '0.0.31', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_inprogress'),
                'ip_dummy_shipment_increment_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'size'  => 255,
                    'comment' => 'Dummy shipment ID'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_shipment'),
                'dummy_increment_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'size'  => 255,
                    'comment' => 'Dummy shipment ID'
                ]
            );
        }

        //0.0.32
        if (version_compare($context->getVersion(), '0.0.32', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_cost_matrix',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'size'  => 400,
                    'comment' => 'cost matrix'
                ]
            );
        }

        //0.0.33
        if (version_compare($context->getVersion(), '0.0.34', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_disable_labels_pregeneration',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'size'  => 50,
                    'comment' => 'Disable shipping labels pre-generation'
                ]
            );
        }
        
        //0.0.35
        if (version_compare($context->getVersion(), '0.0.35', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_export_encoding',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length'  => 20,
                    'comment' => 'Shipping Label Format Export as encoding(ANSI/UTF-8)',
                    'default' => 'utf8'
                ]
            );
        }

        //0.0.36
        if (version_compare($context->getVersion(), '0.0.36', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_orderpreparation_carrier_template'),
                'ct_store_ids',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'size'  => 50,
                    'comment' => 'store ids',
                ]
            );
        }

        $setup->endSetup();
    }

}

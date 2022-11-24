<?php

namespace Yalla\Vendors\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface {

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        
        $tableName = $installer->getTable('yalla_vendors');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'vendor_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                            ], 'ID'
                    )
                    ->addColumn(
                            'vendor_name', Table::TYPE_TEXT, null, ['nullable' => false, 'default' => ''], 'Vendor Name'
                    )
                    ->addColumn(
                            'vennder_email', Table::TYPE_TEXT, null, ['nullable' => false, 'default' => ''], 'Vendor Email'
                    )
                    ->addColumn(
                            'vendor_number', Table::TYPE_TEXT, null, ['nullable' => false, 'default' => ''], 'Vendor Number'
                    )
					->addColumn(
                            'vendor_address', Table::TYPE_TEXT, null, ['nullable' => false, 'default' => ''], 'Vendor Address'
                    )
                    ->addColumn(
                            'created_at', Table::TYPE_DATETIME, null, ['nullable' => false], 'Created At'
                    )
                    ->addColumn(
                            'updated_at', Table::TYPE_DATETIME, null, ['nullable' => true, 'default' => null], 'Updated At'
                    )
                    ->addColumn(
                            'status', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Status'
                    )
                    ->setComment('Yalla Vendors')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }

}

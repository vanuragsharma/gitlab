<?php
namespace Magepow\Flipbook\Setup;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
 public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
 {
   $setup->startSetup();
   $tableName = $setup->getTable('magepow_flipbook');
   if (version_compare($context->getVersion(), '2.0.0', '<')) {
       if ($setup->getConnection()->isTableExists($tableName) == true){
        $connection = $setup->getConnection();
            $fullTextIndex = array('title', 'author', 'description', 'book'); // Column with fulltext index, you can put multiple fields
            $setup->getConnection()->addIndex(
                $tableName,
                $setup->getIdxName($tableName, $fullTextIndex, \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT),
                $fullTextIndex,
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );

        }
    }
    if (version_compare($context->getVersion(), '2.0.1', '<')) {
       if ($setup->getConnection()->isTableExists($tableName) == true){
        $connection = $setup->getConnection();

        $connection->addColumn(
                $setup->getTable('magepow_flipbook'),
                'external_link',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'PDF External link'
                ]
            );

            $fullTextIndex = array('external_link');
            $setup->getConnection()->addIndex(
                $tableName,
                $setup->getIdxName($tableName, $fullTextIndex, \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT),
                $fullTextIndex,
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
    }
  }
}
<?php

namespace BoostMyShop\Margin\Setup;

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
        if (version_compare($context->getVersion(), '0.0.2', '<')) {

            $setup->getConnection()->addColumn(
            $setup->getTable('sales_shipment'),
            'shipping_cost',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '8,2',
                'nullable' => true,
                'comment' => 'Shipping cost',
                'default' => 0
            ]
        );

            $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'shipping_cost',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '8,2',
                'nullable' => true,
                'comment' => 'Shipping cost',
                'default' => 0
            ]
        );

        }

        $setup->endSetup();
    }

}

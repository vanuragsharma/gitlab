<?php

namespace BoostMyShop\AvailabilityStatus\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.3', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('bms_advancedstock_warehouse'),
                'w_availability_delay',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => false,
                    'comment' => 'Availability delay',
                    'default' => 0
                ]
            );

        }

        $setup->endSetup();
    }

}

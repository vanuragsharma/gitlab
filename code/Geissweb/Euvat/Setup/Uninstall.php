<?php
/**
 * ||GEISSWEB| EU VAT Enhanced
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL: https://www.geissweb.de/legal-information/eula
 *
 * DISCLAIMER
 *
 * Do not edit this file if you wish to update the extension in the future. If you wish to customize the extension
 * for your needs please refer to our support for more information.
 *
 * @copyright   Copyright (c) 2015 GEISS Weblösungen (https://www.geissweb.de)
 * @license     https://www.geissweb.de/legal-information/eula GEISSWEB End User License Agreement
 */

namespace Geissweb\Euvat\Setup;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Geissweb\Euvat\Logger\Logger;

/**
 * For uninstallation
 * Class Uninstall
 */
class Uninstall implements UninstallInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Uninstall constructor.
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param Config               $eavConfig
     * @param Logger               $logger
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        Config $eavConfig,
        Logger $logger
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->logger = $logger;
    }

    /**
     * Uninstall Script
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $setup->startSetup();

            // remove vat_validation table
            $setup->getConnection()->dropTable($setup->getTable('vat_validation'));

            // remove the additional address attributes
            /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
            $typeId = $this->eavConfig->getEntityType(AddressMetadataInterface::ENTITY_TYPE_ADDRESS)->getEntityTypeId();
            $customerSetup = $this->customerSetupFactory->create();
            $customerSetup->removeAttribute($typeId, 'vat_trader_name');
            $customerSetup->removeAttribute($typeId, 'vat_trader_address');
            $customerSetup->removeAttribute($typeId, 'vat_trader_company_type');

            // remove additional columns on quote, order and address entity tables
            $checkoutConnection = $setup->getConnection('checkout');
            $quoteAddressTable = $checkoutConnection->getTableName($setup->getTable('quote_address'));
            $salesConnection = $setup->getConnection('sales');
            $orderAddressTable = $salesConnection->getTableName($setup->getTable('sales_order_address'));
            $checkoutConnection->dropColumn($quoteAddressTable, 'vat_trader_name');
            $checkoutConnection->dropColumn($orderAddressTable, 'vat_trader_name');
            $checkoutConnection->dropColumn($setup->getTable('customer_address_entity'), 'vat_trader_name');
            $checkoutConnection->dropColumn($quoteAddressTable, 'vat_trader_address');
            $checkoutConnection->dropColumn($orderAddressTable, 'vat_trader_address');
            $checkoutConnection->dropColumn($setup->getTable('customer_address_entity'), 'vat_trader_address');

            $setup->endSetup();
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
        }
    }
}

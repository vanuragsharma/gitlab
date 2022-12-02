<?php 
namespace Catchers\Custom\Setup;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface 
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        
        if (version_compare($context->getVersion(), '1.0.1', '<')) {       

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $setup->startSetup();

            $attributeCode1 = 'cc_email_shipment';

            $eavSetup->addAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, 'cc_email_shipment', [
                'label' => 'Email for Shipment',
                'required' => false,
                'user_defined' => 1,
                'system' => 0,
                'position' => 101,
                'input' => 'text'
            ]);


            $eavSetup->addAttributeToSet(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                null,
                $attributeCode1);

            $shipEmailId = $this->eavConfig->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $attributeCode1);
            $shipEmailId->setData('used_in_forms', [
                'adminhtml_customer',
                'customer_account_create',
                'customer_account_edit'
            ]);
            $shipEmailId->getResource()->save($shipEmailId);

            $attributeCode2 = 'cc_email_invoice';

            $eavSetup->addAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, 'cc_email_invoice', [
                'label' => 'Email for Invoice',
                'required' => false,
                'user_defined' => 1,
                'system' => 0,
                'position' => 101,
                'input' => 'text'
            ]);

            $eavSetup->addAttributeToSet(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                null,
                $attributeCode2);

            $invoiceEmailId = $this->eavConfig->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $attributeCode2);
            $invoiceEmailId->setData('used_in_forms', [
                'adminhtml_customer',
                'customer_account_create',
                'customer_account_edit'
            ]);
            $invoiceEmailId->getResource()->save($invoiceEmailId);

            $setup->endSetup();
        }
    }
}
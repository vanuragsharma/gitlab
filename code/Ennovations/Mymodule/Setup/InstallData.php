<?php

namespace Ennovations\Mymodule\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
		        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'is_vendor');
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'is_vendor', [
            'group' => 'Product Details',
            'type' => 'int',
            'backend' => '',
            'frontend' => '',
            'sort_order' => 210,
            'label' => 'Vendor',
            'input' => 'select',
            'class' => '',
            'source' => 'Ennovations\Mymodule\Model\Source\Isvendor',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'is_used_in_grid' => true,
            'is_filterable_in_grid' => true,
            'used_in_product_listing' => true,
            'apply_to' => ''
        ]);
    }
}

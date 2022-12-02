<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ShowPriceAfterLogin
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ShowPriceAfterLogin\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class ProductAttributeSave implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    protected $moduleDataSetup;
    
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    protected $_eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
         
        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'show_price');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'call_for_price');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'show_price_customer_group');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'call_for_price_label');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'call_for_price_link');
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'show_price', /* Custom Attribute Code */
            [
                'group' => 'Product Details', /* Group name in which you want
                                                 to display your custom attribute */
                'type' => 'int', /* Data type in which formate your value save in database*/
                'backend' => '',
                'frontend' => '',
                'label' => 'Show Price', /* lablel of your attribute*/
                'input' => 'select',
                'class' => '',
                'source' => '', /* Source of your select type custom attribute options*/
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                                    /*Scope of your attribute */
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        )->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'call_for_price', /* Custom Attribute Code */
            [
                'group' => 'Product Details', /* Group name in which you want
                                                 to display your custom attribute */
                'type' => 'int', /* Data type in which formate your value save in database*/
                'backend' => '',
                'frontend' => '',
                'label' => 'Call For Price', /* lablel of your attribute*/
                'input' => 'radioset',
                'class' => '',
                'source' => '', /* Source of your select type custom attribute options*/
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                                    /*Scope of your attribute */
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        )->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'show_price_customer_group', /* Custom Attribute Code */
            [
                'group' => 'Product Details', /* Group name in which you want
                                                 to display your custom attribute */
                'type' => 'varchar', /* Data type in which formate your value save in database*/
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'frontend' => '',
                'label' => 'Show Price Customer Group', /* lablel of your attribute*/
                'input' => 'multiselect',
                'class' => '',
                'source' => '', /* Source of your select type custom attribute options*/
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                                    /*Scope of your attribute */
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        )->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'call_for_price_label', /* Custom Attribute Code */
            [
                'group' => 'Product Details', /* Group name in which you want
                                                 to display your custom attribute */
                'type' => 'varchar', /* Data type in which formate your value save in database*/
                'backend' => '',
                'frontend' => '',
                'label' => 'Call For Price Label', /* lablel of your attribute*/
                'input' => 'text',
                'class' => '',
                'source' => '', /* Source of your select type custom attribute options*/
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                                    /*Scope of your attribute */
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        )->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'call_for_price_link', /* Custom Attribute Code */
            [
                'group' => 'Product Details', /* Group name in which you want
                                                 to display your custom attribute */
                'type' => 'varchar', /* Data type in which formate your value save in database*/
                'backend' => '',
                'frontend' => '',
                'label' => 'Call For Price Link', /* lablel of your attribute*/
                'input' => 'text',
                'class' => '',
                'source' => '', /* Source of your select type custom attribute options*/
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                                    /*Scope of your attribute */
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}

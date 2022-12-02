<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ShowPriceAfterLogin
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ShowPriceAfterLogin\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory /* For Attribute create  */;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;
    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->_eavSetupFactory = $eavSetupFactory;
        /* assign object to class global variable for use in other class methods */
    }
 
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
         
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
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
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
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
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
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'frontend' => '',
                'label' => 'Show Price Customer Group', /* lablel of your attribute*/
                'input' => 'multiselect',
                'class' => '',
                'source' => '', /* Source of your select type custom attribute options*/
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
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
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
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
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
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
}

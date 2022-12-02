<?php

namespace BoostMyShop\Supplier\Setup;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;


class UpgradeData implements UpgradeDataInterface
{
    protected $eavSetupFactory;
    protected $_productHelper;
    protected $_transitCollectionFactory;
    protected $_productCollectionFactory;
    protected $_productAction;
    protected $_state;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \BoostMyShop\Supplier\Model\Product $productHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Framework\App\State $state,
        \BoostMyShop\Supplier\Model\ResourceModel\Transit\CollectionFactory $transitCollectionFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->_productHelper = $productHelper;
        $this->_transitCollectionFactory = $transitCollectionFactory;
        $this->_productAction = $productAction;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_state = $state;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '0.0.25') < 0)
        {
            //change qty to receive attribute type
            $eavSetup->removeAttribute('catalog_product', 'qty_to_receive');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'qty_to_receive',
                [
                    'type' => 'int',
                    'visible' => false,
                    'required' => false,
                    'default' => 0,
                ]
            );

            //populate qty to receive again
            $productIds = $this->_transitCollectionFactory->create()->init(false)->getAllIds();
            foreach($productIds as $productId)
            {
                $this->_productHelper->updateQuantityToReceive($productId);
            }

        }

        if (version_compare($context->getVersion(), '0.0.31') < 0)
        {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'supply_discontinued',
                [
                    'group' => 'General',
                    'type' => 'int',
                    'input' => 'boolean',
                    'label' => 'Discontinued',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'default' => 0,
                ]
            );
        }


        if (version_compare($context->getVersion(), '0.0.32') < 0)
        {
            try
            {
                $this->_state->setAreaCode('adminhtml');
            }
            catch(\Exception $ex)
            {
                //nothing, just mean that area code is already set
            }

            //init default value for supply_discontinued
            $productIds = $this->_productCollectionFactory->create()->getAllIds();
            $arrays = array_chunk($productIds, 200);
            foreach($arrays as $array)
            {
                $this->_productAction->updateAttributes($array, ['supply_discontinued' => 0], 0);
            }

        }

        $setup->endSetup();
    }

}

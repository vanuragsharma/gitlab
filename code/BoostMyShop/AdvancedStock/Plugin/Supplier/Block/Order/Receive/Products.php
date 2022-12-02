<?php

namespace BoostMyShop\AdvancedStock\Plugin\Supplier\Block\Order\Receive;

use Magento\Backend\App\Area\FrontNameResolver;

class Products
{
    protected $_objectManager;
    protected $_configScope;
    protected $_barcodesCollectionFactory;
    protected $_productCollectionFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Barcodes\CollectionFactory $barcodeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    )
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_barcodesCollectionFactory = $barcodeCollectionFactory;
    }

    public function aroundGetBarcodesJson(\BoostMyShop\Supplier\Block\Order\Receive\Products $subject, $proceed)
    {
        $barcodes = array();

        $config = $this->getObjectManager()->create('BoostMyShop\Supplier\Model\Config');
        $barcodeAttribute = $config->getBarcodeAttribute();
        $orderProductCollectionFactory = $this->getObjectManager()->create('BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory');
        $productIds = $orderProductCollectionFactory->create()->getAlreadyAddedProductIds($subject->getOrder()->getId());

        if ($barcodeAttribute)
        {
            $collection = $this->_productCollectionFactory->create()->addAttributeToSelect($barcodeAttribute)->addFieldToFilter('entity_id', array('in' => $productIds));

            foreach($collection as $item)
            {
                if ($item->getData($barcodeAttribute)){
                    $barcodes[$item->getData($barcodeAttribute)] = $item->getId();
                }
                $barcodes[$this->removePrefix($item->getSku())] = $item->getId();
            }
        }
        $barcodeCollection = $this->_barcodesCollectionFactory->create()
                            ->addFieldToSelect('bac_code')
                            ->addFieldToSelect('bac_product_id')
                            ->addFieldToFilter('bac_product_id', array('in' => $productIds));
        foreach($barcodeCollection as $bac)
        {
            $barcodes[$bac->getData('bac_code')] = $bac->getData('bac_product_id');
        }

        return json_encode($barcodes);
    }

    protected function getObjectManager($forceApplyAreaCode = false)
    {
        if (null == $this->_objectManager) {
            $area = $forceApplyAreaCode ? $forceApplyAreaCode: FrontNameResolver::AREA_CODE;
            $this->_configScope = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Config\ScopeInterface::class);
            $this->_configScope->setCurrentScope($area);
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }

        return $this->_objectManager;
    }

    public function removePrefix($sku)
    {
        $t = explode('_', $sku);
        if (count($t) > 1)
            unset($t[0]);

        return implode('_', $t);
    }
}

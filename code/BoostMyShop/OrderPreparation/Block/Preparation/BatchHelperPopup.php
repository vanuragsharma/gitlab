<?php
namespace BoostMyShop\OrderPreparation\Block\Preparation;

class BatchHelperPopup extends \Magento\Backend\Block\Template
{
    protected $_template = 'OrderPreparation/Preparation/BatchHelperPopup.phtml';

    protected $_preparationRegistry;
    protected $_config = null;
    protected $_batchHelper;
    protected $itemCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\OrderPreparation\Model\Config $config,
        \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
        array $data = [],
        \BoostMyShop\OrderPreparation\Model\BatchHelper $batchHelper,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemCollectionFactory
    )
    {
        parent::__construct($context, $data);
        $this->_config = $config;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_batchHelper = $batchHelper;
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    public function getBatchHelper()
    {
        return $this->_batchHelper;
    }

    public function isBatchEnable()
    {
        return $this->_config->isBatchEnable();
    }

    public function getCellUrl($type, $carrier = null)
    {
        $param["wh_id"] = $this->getCurrentWarehouseId();
        $param["type"] = $type;
        if($carrier)
            $param["carrier"] = $carrier;
        return $this->getUrl("orderpreparation/preparation/createbatch",$param);
    }

    public function getCurrentWarehouseId()
    {
        return $this->_preparationRegistry->getCurrentWarehouseId();
    }

    public function getUniqueSkus($orderIds)
    {
        $collection = $this->itemCollectionFactory->create()->addFieldToSelect(['product_id','sku','qty_ordered'])
                                                       ->addFieldToFilter('order_id', ['in' => $orderIds]);
        $uniqueSkus = [];
        foreach ($collection as $item) {
            if(array_key_exists($item->getproduct_id(), $uniqueSkus))
                $uniqueSkus[$item->getproduct_id()]['qty'] += $item->getqty_ordered();
            else
                $uniqueSkus[$item->getproduct_id()] = ['sku' => $this->removePrefix($item->getsku()), 'qty' => $item->getqty_ordered()];
        }

        return $uniqueSkus;
    }

    public function getcellUrlByProductId($productId, $type, $carrier = null)
    {
        $param['p_id'] = $productId;
        $param["wh_id"] = $this->getCurrentWarehouseId();
        $param["type"] = $type;
        if($carrier)
            $param["carrier"] = $carrier;
        return $this->getUrl("orderpreparation/preparation/createbatch",$param);
    }

    public function removePrefix($sku)
    {
        $t = explode('_', $sku);
        if (count($t) > 1)
            unset($t[0]);
        return implode('_', $t);
    }
}

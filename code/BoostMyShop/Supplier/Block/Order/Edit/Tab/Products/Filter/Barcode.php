<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Filter;


class Barcode extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    protected $_productCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ){
        parent::__construct($context, $resourceHelper, $data);

        $this->_productCollectionFactory = $productCollectionFactory;
    }

   
    public function getCondition()
    {
        if ($this->getValue())
        {
            $productIds = $this->_productCollectionFactory->create()->addAttributeToFilter($this->getColumn()->getIndex(), array("like" => "%".$this->getValue()."%"))->getAllIds();
            return ['in' => $productIds];
        }
    }

}
<?php

namespace BoostMyShop\AdvancedStock\Model;


class Barcodes extends \Magento\Framework\Model\AbstractModel
{
    protected $_config;
    protected $_productFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\Barcodes');
    }

    public function getProductFilter($id){
        return $this->_getResource()->getBarcodesFromProductId($id);
    }

}
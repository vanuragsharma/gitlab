<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab;

class AddProductsSelected extends \Magento\Backend\Block\Template
{

    protected $_template = 'Order/Edit/Tab/AddProductsSelected.phtml';

    protected $_config = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \BoostMyShop\Supplier\Model\Config $config,
                                array $data = [])
    {
        parent::__construct($context, $data);
        $this->_config = $config;
    }

    public function packQtyEnabled(){

        return $this->_config->getSetting('general/pack_quantity');

    }

}

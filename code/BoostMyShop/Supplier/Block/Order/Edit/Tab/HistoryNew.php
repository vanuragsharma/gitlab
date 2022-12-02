<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab;

class HistoryNew extends \Magento\Backend\Block\Template
{

    protected $_template = 'Order/Edit/Tab/HistoryNew.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context, array $data = []
    ){
        parent::__construct($context, $data);
    }
}

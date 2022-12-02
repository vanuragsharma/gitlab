<?php

namespace BoostMyShop\Supplier\Block\Order;


class Reception extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected $_resourceModel;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->removeButton('add');
    }

    public function getTitle()
    {
        return 'Receptions';
    }
}

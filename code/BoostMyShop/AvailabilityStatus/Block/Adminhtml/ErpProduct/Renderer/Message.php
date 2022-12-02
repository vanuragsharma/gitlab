<?php

namespace BoostMyShop\AvailabilityStatus\Block\Adminhtml\ErpProduct\Renderer;

use Magento\Framework\DataObject;

class Message extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_helper;
    protected $_coreRegistry;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\AvailabilityStatus\Model\AvailabilityStatus $helper,
                                \Magento\Framework\Registry $coreRegistry,
                                array $data = [])
    {
        parent::__construct($context, $data);

        $this->_helper = $helper;
        $this->_coreRegistry = $coreRegistry;
    }

    public function render(DataObject $row)
    {
        $data = $this->_helper->getAvailability($this->getProduct(), $row->getstore_id());
        $html = "Date :".$data['date'];
        $html .= "<br>Message :".strip_tags($data['message']);
        return $html;
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

}
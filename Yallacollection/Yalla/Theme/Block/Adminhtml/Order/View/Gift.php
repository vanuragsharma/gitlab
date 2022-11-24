<?php

namespace Yalla\Theme\Block\Adminhtml\Order\View;


class Gift extends \Magento\Backend\Block\Template
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }



    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }
    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getGiftWrap()
    {
        $giftWrap = $this->getOrder()->getData('giftwrap');
        return $giftWrap;
    }

    

}



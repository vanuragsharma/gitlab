<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Renderer;

use Magento\Framework\DataObject;

class Actions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_config = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context,
                                \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
                                \BoostMyShop\OrderPreparation\Model\Config $config,
                                array $data = [])
    {
        parent::__construct($context, $data);
        $this->_config = $config;
        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
    }

    public function render(DataObject $order)
    {
        $html = [];

        $actions = [];
        $actions[] = ['label' => __('View'), 'url' => $this->getUrl('sales/order/view', ['order_id' => $order->getId()]), 'target' => ''];
        if(!$this->_config->isBatchEnable())
            $actions[] = ['label' => __('Prepare'), 'url' => $this->getUrl('*/*/addOrder', ['order_id' => $order->getId()]), 'target' => ''];

        foreach($actions as $action)
        {
            $html[] = '<a href="'.$action['url'].'" target="'.$action['target'].'">'.$action['label'].'</a>';
        }

        return implode('<br>', $html);
    }


}
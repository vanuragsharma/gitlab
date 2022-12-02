<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Renderer;

use Magento\Framework\DataObject;

class InProgressProducts extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_orderItemCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context,
                                \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
                                array $data = [])
    {
        parent::__construct($context, $data);

        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
    }

    public function render(DataObject $inProgress)
    {
        $html = [];

        foreach ($inProgress->getAllItems() as $item) {
            $html[] .= $this->renderItem($item);
        }

        return implode('<br>', $html);
    }

    public function renderExport(DataObject $inProgress)
    {
        $html = [];

        foreach ($inProgress->getAllItems() as $item) {
            $html[] .= $this->renderItemExport($item);
        }

        return implode(', ', $html);
    }

    protected function renderItem($item)
    {
        return $item->getipi_qty().'x '.$item->getSku().' - '.$item->getName();
    }

    protected function renderItemExport($item)
    {
        return $item->getipi_qty().' | '.$this->cleanReference($item->getSku()).' | '.$item->getName();
    }

    public function cleanReference($reference)
    {
        $t = explode('_', $reference);
        if (isset($t[0]) && count($t) > 1)
            unset($t[0]);
        return implode('_', $t);
    }
}
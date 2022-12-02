<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class Warehouses extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_warehouses;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\OrderPreparation\Model\Config\Source\Warehouses $warehouses,
                                array $data = [])
    {
        parent::__construct($context, $data);

        $this->_warehouses = $warehouses->toOptionArray();
    }

    public function render(DataObject $row)
    {
        $html = [];
        if ($row->getct_warehouse_ids())
        {
            $warehouseIds = unserialize($row->getct_warehouse_ids());
            foreach($warehouseIds as $warehouseId)
            {
                $warehouseName = (isset($this->_warehouses[$warehouseId]) ? $this->_warehouses[$warehouseId] : '');
                if ($warehouseId == '*')
                    $warehouseName = __('All');
                $html[] = $warehouseName;
            }
        }
        return implode(', ', $html);
    }
}
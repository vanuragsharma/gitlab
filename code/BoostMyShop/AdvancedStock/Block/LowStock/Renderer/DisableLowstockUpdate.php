<?php

namespace BoostMyShop\AdvancedStock\Block\LowStock\Renderer;

use Magento\Framework\DataObject;

class DisableLowstockUpdate extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_config;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_config = $config;
    }

    public function render(DataObject $row)
    {
        $html = '';
        $selectName = 'lowstock['.$row->getwi_id().'][disable_lowstock_update]';
        $selectId = 'lowstock_'.$row->getwi_id().'_disable_lowstock_update';
        $onchange = 'lowStock.logChange(\''.$row->getwi_id().'\', \'disable_lowstock_update\', this.selectedIndex);';

        $html .= '<select name="'.$selectName.'" id="'.$selectId.'" onchange="'.$onchange.'">';
        $html .= '<option value="0" '.($row->getdisable_lowstock_update() ? '' : ' selected ').'>'.__('No').'</option>';
        $html .= '<option value="1" '.($row->getdisable_lowstock_update() ? ' selected ' : '').'>'.__('Yes').'</option>';
        $html .= '</select>';

        return $html;
    }

    public function renderExport(DataObject $row)
    {
        return $row->getdisable_lowstock_update() ? __('Yes') : __('No');
    }
}

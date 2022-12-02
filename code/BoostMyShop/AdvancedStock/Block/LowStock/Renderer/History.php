<?php

namespace BoostMyShop\AdvancedStock\Block\LowStock\Renderer;

use Magento\Framework\DataObject;

class History extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $html = '<table border="0"><tr>';
        for($i=1;$i<=3;$i++)
        {
            $salesCount = $row->getData('sh_range_'.$i);
            $weeks = $this->_config->getSetting('stock_level/history_range_'.$i);
            $salesPerWeek = number_format($salesCount / $weeks, 1, '.', '');

            $html .= '<td style="width: 100px; border: solid 1px #cccccc;" align="center">';
            $html .= $salesCount;
            $html .= '<br><i>'.$salesPerWeek.'/w</i>';
            $html .= '</td>';
        }

        $html .= '</tr></table>';

        return $html;
    }
    
    public function renderExport(DataObject $row)
    {
        $html ='';

        for($i=1;$i<=3;$i++)
        {
            $html .= $row->getData('sh_range_'.$i) ? : '0';
            if($i<3)
                $html .= '/';
        }

        return $html;
    }


}

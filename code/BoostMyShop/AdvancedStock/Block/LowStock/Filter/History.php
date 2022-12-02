<?php

namespace BoostMyShop\AdvancedStock\Block\LowStock\Filter;

class History extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    protected $_config;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \Magento\Framework\DB\Helper $resourceHelper,
                                \BoostMyShop\AdvancedStock\Model\Config $config,
                                array $data = [])
    {
        parent::__construct($context, $resourceHelper, $data);

        $this->_config = $config;
    }

    public function getHtml()
    {
        $html = '<table border="0"><tr>';
        for($i=1;$i<=3;$i++)
        {
            $html .= '<td style="width: 100px; border: solid 1px #cccccc;" align="center">'.$this->_config->getSetting('stock_level/history_range_'.$i).' '.__('weeks').'</td>';
        }

        $html .= '</tr></table>';

        return $html;
    }

}

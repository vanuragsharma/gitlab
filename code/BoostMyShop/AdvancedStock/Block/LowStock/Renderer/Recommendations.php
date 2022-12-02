<?php

namespace BoostMyShop\AdvancedStock\Block\LowStock\Renderer;

use Magento\Framework\DataObject;

class Recommendations extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_lowStockLevelUpdater;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\AdvancedStock\Model\LowStockLevelUpdater $lowStockLevelUpdater,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_lowStockLevelUpdater = $lowStockLevelUpdater;
    }

    public function render(DataObject $row)
    {
        $reco = $this->_lowStockLevelUpdater->getRecommendations($row, $row->getData('w_ignore_sales_below_1'));

        $html =  'Warning : '.$reco['warning_stock_level'];
        $html .= '<br>Ideal : '.$reco['ideal_stock_level'];

        return $html;
    }

    public function renderExport(DataObject $row)
    {
        $html ='';
        return $html;
    }


}

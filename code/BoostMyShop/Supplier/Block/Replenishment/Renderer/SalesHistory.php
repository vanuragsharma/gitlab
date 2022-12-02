<?php

namespace BoostMyShop\Supplier\Block\Replenishment\Renderer;

use Magento\Framework\DataObject;
use Magento\Backend\App\Area\FrontNameResolver;

class SalesHistory extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_configScope;
    protected $_objectManager;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $html = '<table border="0"><tr>';
        for($i=1;$i<=3;$i++)
        {
            $salesCount = $row->getData('sh_sum_range_'.$i);
            $config = $this->getObjectManager()->create('BoostMyShop\AdvancedStock\Model\Config');
            $weeks = $config->getSetting('stock_level/history_range_'.$i);
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
            $html .= $row->getData('sh_sum_range_'.$i) ? : '0';
            if($i<3)
                $html .= '/';
        }

        return $html;
    }

    protected function getObjectManager()
    {
        if (null == $this->_objectManager)
        {
            $area = FrontNameResolver::AREA_CODE;
            $this->_configScope = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Config\ScopeInterface::class);
            $this->_configScope->setCurrentScope($area);
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }

        return $this->_objectManager;
    }
}

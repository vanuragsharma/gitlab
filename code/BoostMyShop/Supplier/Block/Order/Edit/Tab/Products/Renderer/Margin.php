<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class Margin extends AbstractRenderer
{

    public function render(DataObject $row)
    {

        $sellPrice = $this->getSellPrice($row);
        $cost = $row->getUnitPriceBaseWithCost();

        $margin = $sellPrice - $cost;

        if ($sellPrice > 0)
            $marginPercent = number_format($margin / $sellPrice * 100, 0, '.', '');
        else
            $marginPercent = 0;

        $html = '<div style="white-space:nowrap;"><b>'.__('Price').'</b>: '.$this->getCurrency()->format($sellPrice).'<br>';
        $html .= '<b>'.__('Cost').'</b>: '.$this->getCurrency()->format($cost).'<br>';
        $html .= '<b>'.__('Margin').'</b>: <font color="'.($margin < 0 ? 'red' : '').'">'.$this->getCurrency()->format($margin).' <i>('.$marginPercent.'%)</i></font></div>';

        return $html;
    }

    public function getSellPrice($row)
    {
        //todo : manage tax settings and catalog price rules ?
        $value = $row->getProduct()->getPrice();
        return $value;
    }

    public function getCurrency()
    {
        if (!$this->_currency) {
            $websiteId = $this->getOrder()->getpo_website_id();
            $storeId = $this->_config->getStoreIdFromWebsiteId($websiteId);
                $this->_currency = $this->_currencyFactory->create()->load($this->_config->getGlobalSetting('currency/options/base', $storeId));
        }
        return $this->_currency;
    }

}
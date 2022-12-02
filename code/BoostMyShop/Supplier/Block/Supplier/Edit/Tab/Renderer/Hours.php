<?php

namespace BoostMyShop\Supplier\Block\Supplier\Edit\Tab\Renderer;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Escaper;

class Hours extends \Magento\Framework\Data\Form\Element\Checkbox
{
    public function getElementHtml()
    {
        $html = '';
        $htmlId = $this->getHtmlId();

        $beforeElementHtml = $this->getBeforeElementHtml();
        if ($beforeElementHtml) {
            $html .= '<label class="addbefore" for="' . $htmlId . '">' . $beforeElementHtml . '</label>';
        }

        if (is_array($this->getValue())) {
            foreach ($this->getValue() as $value) {
                $html .= $this->getHtmlForInputByValue($this->_escape($value));
            }
        } else {
            $html .= $this->getHtmlForInputByValue($this->getEscapedValue());
        }

        $afterElementJs = $this->getAfterElementJs();
        if ($afterElementJs) {
            $html .= $afterElementJs;
        }

        $afterElementHtml = $this->getAfterElementHtml();
        if ($afterElementHtml) {
            $html .= '<label class="addafter" for="' . $htmlId . '">' . $afterElementHtml . '</label>';
        }

        return $html;
    }

    private function getHtmlForInputByValue($value)
    {
        $values = explode(",", $value);
        $hours = [
            1 => "01:00",
            2 => "02:00",
            3 => "03:00",
            4 => "04:00",
            5 => "05:00",
            6 => "06:00",
            7 => "07:00",
            8 => "08:00",
            9 => "09:00",
            10 => "10:00",
            11 => "11:00",
            12 => "12:00",
            13 => "13:00",
            14 => "14:00",
            15 => "15:00",
            16 => "16:00",
            17 => "17:00",
            18 => "18:00",
            19 => "19:00",
            20 => "20:00",
            21 => "21:00",
            22 => "22:00",
            23 => "23:00",
            24 => "24:00",
        ];
        $html = '<table id="supplier_sup_delayed_notification_hours_table"><tbody><tr>';
        for ($i= 1; $i <= 24; $i++)
        {
            $checked = (in_array($i, $values))?"checked='checked'":'';
            $html .= '<td><input style="height: 16px;" id="' . $this->getHtmlId() . '" name="' . $this->getName() . '[]" ' . $this->_getUiId()
                . ' value="' . $i . '" '.$checked.' ' . $this->serialize($this->getHtmlAttributes()) . '/>'.$hours[$i]. '<span style="margin-right: 20px;">&nbsp</span></td>';
            if($i%6 == 0)
                $html .= '</tr><tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }
}
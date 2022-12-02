<?php

namespace BoostMyShop\OrderPreparation\Model\Config\Source;

class PickingPdfFormats implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            '420:595:' => 'A5',
            '595:842:'=>'A4'
        ];
    }

}

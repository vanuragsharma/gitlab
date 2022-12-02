<?php

namespace BoostMyShop\OrderPreparation\Model\Config\Source;

class BatchPdfFormats implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            '420:595:' => 'SIZE_A5',
            '595:842:'=>'SIZE_A4'
        ];
    }

}

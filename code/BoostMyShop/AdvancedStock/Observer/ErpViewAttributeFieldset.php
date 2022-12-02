<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ErpViewAttributeFieldset implements ObserverInterface
{
    public function execute(EventObserver $observer)
    {
        $form = $observer->getEvent()->getform();
        $product = $observer->getEvent()->getproduct();
        $Fieldset = $form->addFieldset('additional_barcode_fieldset', ['legend' => __('Additional Barcodes')]);

        $attributes = $this->getAttributes();

        $Fieldset->addType(
            'additional_barcode',
            'BoostMyShop\AdvancedStock\Block\Product\Edit\Tab\AttributeFieldset'
        );
        foreach($attributes as $attribute)
        {
            $Fieldset->addField(
                $attribute['code'],
                $attribute['type'],
                [
                    'name' => 'attributes['.$attribute['code'].']',
                    'label' => __($attribute['label']),
                    'id' => $attribute['code'],
                    'title' => __($attribute['label']),
                    'value' => $attribute['value'],
                    'options' => (isset($attribute['options']) ? $attribute['options'] : []),
                ]
            );
        }

        return $this;
    }

    public function getAttributes()
    {
        $attributes = [];

        // additional barcode
        $attributes[] = [
            'code' => 'additional_barcode',
            'label' => __('Barcodes'),
            'type' => 'additional_barcode',
            'value' => '',
            'options' => []
        ];
        return $attributes;

    }
}

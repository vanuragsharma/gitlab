<?php

namespace Yalla\Theme\Block\Magento\Catalog\Block\Product\View;

class Description extends \Magento\Catalog\Block\Product\View\Description
{
 
public function getAdditionalData(array $excludeAttr = [])
{
    $data = [];
    $product = $this->getProduct();
    $attributes = $product->getAttributes();


    foreach ($attributes as $attribute) {
        if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
            $value = $attribute->getFrontend()->getValue($product);

            if (!$product->hasData($attribute->getAttributeCode())) {
                $value = __('N/A');
            } elseif ((string)$value == '') {
                $value = __('No');
            } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                $value = $this->priceCurrency->convertAndFormat($value);
            }

            if (($value instanceof Phrase || is_string($value)) && strlen($value)) {
                $data[$attribute->getAttributeCode()] = [
                    'label' => __($attribute->getStoreLabel()),
                    'value' => $value,
                    'code' => $attribute->getAttributeCode(),
                ];
            }
        }
    }

    return $data;
}

}
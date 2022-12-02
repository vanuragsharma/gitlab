<?php

/**
 * Product:       Xtento_ProductExport (2.3.4)
 * ID:            SqKTqBwu3nZ/aKil8RJwNgaZGd9tkMP9MnznEcnkMMM=
 * Packaged:      2017-07-19T07:45:17+00:00
 * Last Modified: 2017-03-06T13:53:09+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Config/ConfigDataConverter.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Config;

class ConfigDataConverter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $classes = [];
        foreach ($source->getElementsByTagName('export') as $exportClass) {
            $id = $exportClass->getAttribute('id');
            $classes[$id] = [
                'class' => $exportClass->getAttribute('class'),
                'profile_ids' => !empty($exportClass->getAttribute('profile_ids')) ? $exportClass->getAttribute('profile_ids') : false
            ];
        }
        return [
            'classes' => $classes,
        ];
    }
}

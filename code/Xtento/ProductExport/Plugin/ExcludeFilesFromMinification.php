<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            DzC2eFiDpwD50/GgVbHWZ8tC9BZhoUDH2be4JBMyIeM=
 * Last Modified: 2019-01-07T19:44:55+00:00
 * File:          app/code/Xtento/ProductExport/Plugin/ExcludeFilesFromMinification.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Plugin;

use Magento\Framework\View\Asset\Minification;

class ExcludeFilesFromMinification
{
    public function aroundGetExcludes(Minification $subject, callable $proceed, $contentType)
    {
        $result = $proceed($contentType);
        if ($contentType != 'js') {
            return $result;
        }
        $result[] = 'Xtento_ProductExport/js/ace/mode-xml';
        $result[] = 'Xtento_ProductExport/js/ace/theme-eclipse';
        return $result;
    }
}
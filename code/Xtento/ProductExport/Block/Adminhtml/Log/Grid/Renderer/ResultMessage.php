<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            DzC2eFiDpwD50/GgVbHWZ8tC9BZhoUDH2be4JBMyIeM=
 * Last Modified: 2021-01-15T20:37:30+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Log/Grid/Renderer/ResultMessage.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Log\Grid\Renderer;

class ResultMessage extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        return nl2br(parent::_getValue($row));
    }
}

<?php

/**
 * Product:       Xtento_ProductExport (2.3.4)
 * ID:            SqKTqBwu3nZ/aKil8RJwNgaZGd9tkMP9MnznEcnkMMM=
 * Packaged:      2017-07-19T07:45:17+00:00
 * Last Modified: 2017-03-06T13:52:53+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Config/SchemaLocator.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Config;

use Magento\Framework\Module\Dir;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $_schema;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $this->_schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Xtento_ProductExport') . '/' . 'xtento/productexport_data.xsd';
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerFileSchema()
    {
        return null;
    }
}

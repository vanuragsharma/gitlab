<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            DzC2eFiDpwD50/GgVbHWZ8tC9BZhoUDH2be4JBMyIeM=
 * Last Modified: 2020-06-05T18:02:02+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Destination/Grid/Renderer/Configuration.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Destination\Grid\Renderer;

class Configuration extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render destination configuration
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $configuration = [];
        if ($row->getType() == \Xtento\ProductExport\Model\Destination::TYPE_LOCAL) {
            $configuration['directory'] = $row->getPath();
        }
        if ($row->getType() == \Xtento\ProductExport\Model\Destination::TYPE_FTP || $row->getType(
            ) == \Xtento\ProductExport\Model\Destination::TYPE_SFTP
        ) {
            $configuration['server'] = $row->getHostname() . ':' . $row->getPort();
            $configuration['username'] = $row->getUsername();
            $configuration['path'] = $row->getPath();
        }
        if ($row->getType() == \Xtento\ProductExport\Model\Destination::TYPE_EMAIL) {
            $configuration['from'] = $row->getEmailSender();
            $configuration['to'] = $row->getEmailRecipient();
            $configuration['bcc'] = $row->getEmailBcc();
            $configuration['subject'] = $row->getEmailSubject();
        }
        if ($row->getType() == \Xtento\ProductExport\Model\Destination::TYPE_CUSTOM) {
            $configuration['class'] = $row->getCustomClass();
        }
        if ($row->getType() == \Xtento\ProductExport\Model\Destination::TYPE_WEBSERVICE) {
            $configuration['class'] = 'Webservice';
            $configuration['function'] = $row->getCustomFunction();
        }
        if (!empty($configuration)) {
            $configurationHtml = '';
            foreach ($configuration as $key => $value) {
                $configurationHtml .= __(ucfirst($key)) . ': <i>' . $this->escapeHtml($value) . '</i><br/>';
            }
            return $configurationHtml;
        } else {
            return '---';
        }
    }
}

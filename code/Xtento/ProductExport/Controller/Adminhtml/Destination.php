<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            DzC2eFiDpwD50/GgVbHWZ8tC9BZhoUDH2be4JBMyIeM=
 * Last Modified: 2016-04-15T20:15:44+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Destination.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml;

abstract class Destination extends \Xtento\ProductExport\Controller\Adminhtml\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Xtento\ProductExport\Model\DestinationFactory
     */
    protected $destinationFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\ProductExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\ProductExport\Model\DestinationFactory $destinationFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\ProductExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\ProductExport\Model\DestinationFactory $destinationFactory
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $scopeConfig);
        $this->registry = $registry;
        $this->escaper = $escaper;
        $this->destinationFactory = $destinationFactory;
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Xtento_ProductExport::destination');
    }

    /**
     * @param $resultPage \Magento\Backend\Model\View\Result\Page
     */
    protected function updateMenu($resultPage)
    {
        $resultPage->setActiveMenu('Xtento_ProductExport::destination');
        $resultPage->addBreadcrumb(__('Products'), __('Products'));
        $resultPage->addBreadcrumb(__('Export Destinations'), __('Export Destinations'));
        $resultPage->getConfig()->getTitle()->prepend(__('Product Export - Destinations'));
    }
}

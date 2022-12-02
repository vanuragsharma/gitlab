<?php

/**
 * Product:       Xtento_XtCore
 * ID:            DzC2eFiDpwD50/GgVbHWZ8tC9BZhoUDH2be4JBMyIeM=
 * Last Modified: 2019-12-20T10:13:28+00:00
 * File:          app/code/Xtento/XtCore/Model/System/Config/Backend/Server.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Model\System\Config\Backend;

class Server extends \Magento\Framework\App\Config\Value
{
    protected $version = '';
    protected $module = '';
    protected $extId = '';
    protected $configPath = '';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Xtento\XtCore\Helper\Server
     */
    protected $serverHelper;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Module list
     *
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * Server constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Xtento\XtCore\Helper\Server $serverHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Xtento\XtCore\Helper\Server $serverHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->request = $request;
        $this->serverHelper = $serverHelper;
        $this->urlBuilder = $urlBuilder;
        $this->moduleList = $moduleList;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function afterLoad()
    {
        $sName1 = $this->serverHelper->getFirstName();
        $sName2 = $this->serverHelper->getSecondName();
        $this->setValue(
            base64_encode(
                base64_encode(
                    base64_encode(
                        $this->extId . ';' . trim($this->_config->getValue($this->configPath . 'serial')) . ';'
                        . $sName2 . ';;;;'
                        . $this->request->getServer('SERVER_ADDR') . ';' . $sName1 . ';' . $this->version . ';'
                        . $this->_config->getValue($this->configPath . 'enabled') . ';'
                        . $this->moduleList->getOne($this->module)['setup_version'] . ';M2'
                    )
                )
            )
        );
    }

    public function beforeSave()
    {
        $this->_registry->register(
            'xtento_configuration_updated',
            [
                'module' => $this->module,
                'ext_id' => $this->extId,
                'config_path' => $this->configPath
            ],
            true
        );
        $this->setValue(''); // No need to save this in the DB
    }
}

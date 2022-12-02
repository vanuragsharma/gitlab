<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            DzC2eFiDpwD50/GgVbHWZ8tC9BZhoUDH2be4JBMyIeM=
 * Last Modified: 2020-07-26T19:06:55+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Profile/Fields.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Profile;

class Fields extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Xtento\ProductExport\Model\Output\Xml\Writer
     */
    protected $xmlWriter;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Fields constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Xtento\ProductExport\Model\Output\Xml\Writer $xmlWriter
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Xtento\ProductExport\Model\Output\Xml\Writer $xmlWriter,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->xmlWriter = $xmlWriter;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    public function getFieldJson()
    {
        $export = $this->objectManager->create('Xtento\ProductExport\Model\Export\Entity\\' . ucfirst($this->registry->registry('productexport_profile')->getEntity()));
        $export->setShowEmptyFields(1);
        $export->setProfile($this->registry->registry('productexport_profile'));
        $filterField = $this->registry->registry('productexport_profile')->getEntity() == \Xtento\ProductExport\Model\Export::ENTITY_REVIEW ? 'main_table.review_id': 'entity_id';
        if ($this->registry->registry('productexport_profile')->getEntity() == \Xtento\ProductExport\Model\Export::ENTITY_PRODUCT) {
            // Check if ID doesn't exist, if so, try to load by SKU
            try {
                $this->productRepository->getById(explode(",", $this->getTestId())[0]);
                $filters[] = [$filterField => ['in' => explode(",", $this->getTestId())]];
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                // Load by SKU instead
                $filters[] = ['sku' => $this->getTestId()];
            }
        } else {
            $filters[] = [$filterField => ['in' => explode(",", $this->getTestId())]];
        }
        $export->setCollectionFilters($filters);
        $returnArray = $export->runExport();
        if (empty($returnArray)) {
            return false;
        }
        return \Zend_Json::encode($this->prepareJsonArray($returnArray));
    }

    /*
     * Convert Array into EXTJS TreePanel JSON
     */
    protected function prepareJsonArray($array, $parentKey = '')
    {
        static $depth = 0;
        $newArray = [];

        $depth++;
        if ($depth >= 250) {
            return '';
        }

        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $key = $this->xmlWriter->handleSpecialParentKeys($key, $parentKey);
                $newArray[] = ['text' => $key, 'leaf' => false, 'expanded' => true, 'cls' => 'x-tree-noicon', 'children' => $this->prepareJsonArray($val, $key)];
            } else {
                if ($val == '') {
                    $val = __('NULL');
                }
                if (function_exists('mb_convert_encoding')) {
                    try {
                        $val = mb_convert_encoding($val, 'UTF-8', 'auto');
                    } catch (\Exception $e) {}
                }
                $newArray[] = ['text' => $key, 'leaf' => false, 'cls' => 'x-tree-noicon', 'children' => [['text' => $val, 'leaf' => true, 'cls' => 'x-tree-noicon']]];
            }
        }
        return $newArray;
    }

    public function getTestId()
    {
        return urldecode($this->getRequest()->getParam('test_id'));
    }

    public function getRegistry()
    {
        return $this->registry;
    }
}
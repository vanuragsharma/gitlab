<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_carrierList;
    protected $_templateType;
    protected $_websiteType;
    protected $_warehouseList;
    protected $_configFactory;
    protected $_uniqueProduct;
    protected $_singleProduct;
    protected $_multipleProduct;
    protected $_storeCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\ShippingMethod $carrierList,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Type $templateType,
        \BoostMyShop\OrderPreparation\Model\Source\Website $websiteType,
        \BoostMyShop\OrderPreparation\Model\Config\Source\WarehousesAll $warehouseList,
        \BoostMyShop\OrderPreparation\Model\ConfigFactory $configFactory,
        \BoostMyShop\OrderPreparation\Model\Batch\Type\UniqueProduct $uniqueProduct,
        \BoostMyShop\OrderPreparation\Model\Batch\Type\SingleProduct $singleProduct,
        \BoostMyShop\OrderPreparation\Model\Batch\Type\MultipleProduct $multipleProduct,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        array $data = []
    ) {
        $this->_carrierList  = $carrierList;
        $this->_templateType  = $templateType;
        $this->_websiteType  = $websiteType;
        $this->_warehouseList  = $warehouseList;
        $this->_configFactory  = $configFactory;
        $this->_uniqueProduct = $uniqueProduct;
        $this->_singleProduct = $singleProduct;
        $this->_multipleProduct = $multipleProduct;
        $this->_storeCollectionFactory = $storeCollectionFactory;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $model = $this->_coreRegistry->registry('current_carrier_template');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('template_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Main')]);

        if ($model->getId()) {
            $baseFieldset->addField('ct_id', 'hidden', ['name' => 'ct_id']);
        }

        $baseFieldset->addField(
            'ct_name',
            'text',
            [
                'name' => 'ct_name',
                'label' => __('Name'),
                'id' => 'ct_name',
                'title' => __('Name'),
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'ct_disabled',
            'select',
            [
                'name' => 'ct_disabled',
                'label' => __('Status'),
                'id' => 'ct_disabled',
                'title' => __('Status'),
                'class' => 'input-select',
                'options' => ['0' => __('Active'), '1' => __('Inactive')]   //strange, i agree but 0 = active
            ]
        );

        $baseFieldset->addField(
            'ct_type',
            'select',
            [
                'name' => 'ct_type',
                'label' => __('Type'),
                'id' => 'ct_type',
                'title' => __('Type'),
                'class' => 'input-select',
                'options' => $this->_templateType->toOptionArray()
            ]
        );

        $baseFieldset->addField(
            'ct_website_id',
            'select',
            [
                'name' => 'ct_website_id',
                'label' => __('Website'),
                'id' => 'ct_website_id',
                'title' => __('Website'),
                'class' => 'input-select',
                'options' => $this->_websiteType->toArray()
            ]
        );

        $baseFieldset->addField(
            'ct_shipping_methods',
            'multiselect',
            [
                'name' => 'ct_shipping_methods',
                'label' => __('Associated shipping methods'),
                'id' => 'ct_shipping_methods',
                'title' => __('Associated shipping methods'),
                'required' => false,
                'values'    => $this->_carrierList->toOptionArray(),
                'note' => __('This shipping template will only apply to orders having these shipping methods')
            ]
        );

        $baseFieldset->addField(
            'ct_warehouse_ids',
            'multiselect',
            [
                'name' => 'ct_warehouse_ids',
                'label' => __('Warehouses'),
                'id' => 'ct_warehouse_ids',
                'title' => __('Warehouses'),
                'required' => false,
                'values'    => $this->getWarehouses(),
                'note' => __('This shipping template will only apply to shipments assigned to these warehouses')
            ]
        );

        $baseFieldset->addField(
            'ct_store_ids',
            'multiselect',
            [
                'name' => 'ct_store_ids',
                'label' => __('Stores'),
                'id' => 'ct_store_ids',
                'title' => __('Stores'),
                'required' => false,
                'values'    => $this->getStores($model->getct_website_id()),
                'note' => __('This shipping template will only apply to shipments assigned to these stores')
            ]
        );


        if ($this->_configFactory->create()->isBatchEnable()) {
            $baseFieldset->addField(
                'ct_disable_labels_pregeneration',
                'multiselect',
                [
                    'name' => 'ct_disable_labels_pregeneration',
                    'label' => __('Disable labels pre-generation'),
                    'id' => 'ct_disable_labels_pregeneration',
                    'title' => __('Disable labels pre-generation'),
                    'required' => false,
                    'values'    => $this->getBatchTypes(),
                    'note' => __('Labels will not be pre-generated for orders batch type(s) selected')
                ]
            );
        }

        $manifestFieldset = $form->addFieldset('manifest_fieldset', ['legend' => __('Manifest')]);

        $manifestFieldset->addField(
            'ct_manifest_freetext',
            'textarea',
            [
                'name' => 'ct_manifest_freetext',
                'label' => __('Free text'),
                'id' => 'ct_manifest_freetext',
                'title' => __('Free text'),
                'required' => false,
                'note' => __('5 lines maximum')
            ]
        );

        $data = $model->getData();

        if (isset($data['ct_shipping_methods']))
            $data['ct_shipping_methods'] = unserialize($data['ct_shipping_methods']);
        if (isset($data['ct_warehouse_ids']))
            $data['ct_warehouse_ids'] = unserialize($data['ct_warehouse_ids']);
        if (isset($data['ct_store_ids']))
            $data['ct_store_ids'] = unserialize($data['ct_store_ids']);
        if (isset($data['ct_disable_labels_pregeneration']))
            $data['ct_disable_labels_pregeneration'] = unserialize($data['ct_disable_labels_pregeneration']);

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getWarehouses()
    {
        $warehouses = [];
        $warehouses[] = ['value' => '*', 'label' => __('All')];

        foreach($this->_warehouseList->toOptionArray() as $whId => $whName)
            $warehouses[] = ['value' => $whId, 'label' => $whName];

        return $warehouses;
    }

    protected function getStores($websiteId)
    {
        $stores = [];
        $collection = $this->_storeCollectionFactory->create()->addFieldToFilter('website_id', $websiteId);
        $stores[] = ['value' => '*', 'label' => __('All')];

        foreach($collection as $store)
        {
            $stores[] = ['value' => $store->getstore_id(), 'label' => $store->getname()];
        }

        return $stores;
    }

    protected function getBatchTypes()
    {
        $types[] = ['value' => $this->_uniqueProduct->getCode(), 'label' => __('Batches').' '.$this->_uniqueProduct->getName()];
        $types[] = ['value' => $this->_singleProduct->getCode(), 'label' => __('Batches').' '.$this->_singleProduct->getName()];
        $types[] = ['value' => $this->_multipleProduct->getCode(), 'label' => __('Batches').' '.$this->_multipleProduct->getName()];

        return $types;
    }

}

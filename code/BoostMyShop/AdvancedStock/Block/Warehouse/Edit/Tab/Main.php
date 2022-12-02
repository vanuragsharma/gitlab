<?php

namespace BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_LocaleLists;
    protected $_countryLists;
    protected $_config;
    protected $_websiteCollectionFactory;
    protected $_fulfillmentMethodListFactory;

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
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Warehouse\FullfilmentMethodFactory $fulfillmentMethodListFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        array $data = []
    ) {
        $this->_LocaleLists = $localeLists;
        $this->_countryLists = $countryCollectionFactory;
        $this->_config = $config;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_fulfillmentMethodListFactory = $fulfillmentMethodListFactory;

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
        $model = $this->_coreRegistry->registry('current_warehouse');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('warehouse_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Main')]);

        if ($model->getId()) {
            $baseFieldset->addField('w_id', 'hidden', ['name' => 'w_id']);
        }

        $baseFieldset->addField(
            'w_name',
            'text',
            [
                'name' => 'w_name',
                'label' => __('Name'),
                'id' => 'w_name',
                'title' => __('Name'),
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'w_contact',
            'text',
            [
                'name' => 'w_contact',
                'label' => __('Contact'),
                'id' => 'w_contact',
                'title' => __('Contact'),
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'w_email',
            'text',
            [
                'name' => 'w_email',
                'label' => __('Email'),
                'id' => 'w_email',
                'title' => __('Email'),
                'class' => 'validate-email',
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'w_is_active',
            'select',
            [
                'name' => 'w_is_active',
                'label' => __('Status'),
                'id' => 'w_is_active',
                'title' => __('Status'),
                'class' => 'input-select',
                'options' => ['1' => __('Active'), '0' => __('Inactive')],
                'note'  => __('If the warehouse is disabled, it is hidden from the other screens')
            ]
        );


        $baseFieldset->addField(
            'w_fulfilment_method',
            'select',
            [
                'name' => 'w_fulfilment_method',
                'label' => __('Fulfillment method'),
                'id' => 'w_fulfilment_method',
                'title' => __('Fulfillment method'),
                'class' => 'input-select',
                'options' => $this->_fulfillmentMethodListFactory->create()->toOptionArray(),
            ]
        );

        $baseFieldset->addField(
            'w_disable_stock_movement',
            'select',
            [
                'name' => 'w_disable_stock_movement',
                'label' => __('Disable stock movements'),
                'id' => 'w_disable_stock_movement',
                'title' => __('Disable stock movements'),
                'class' => 'input-select',
                'options' => ['0' => __('No'), '1' => __('Yes')],
                'note' => __('Use this option with care')
            ]
        );

        $baseFieldset->addField(
            'w_reset_location_on_oos',
            'select',
            [
                'name' => 'w_reset_location_on_oos',
                'label' => __('Reset product location when stock=0'),
                'id' => 'w_reset_location_on_oos',
                'title' => __('Reset product location when stock=0'),
                'class' => 'input-select',
                'options' => ['0' => __('No'), '1' => __('Yes')],
            ]
        );

        $baseFieldset->addField(
            'w_is_primary',
            'select',
            [
                'name' => 'w_is_primary',
                'label' => __('Primary'),
                'id' => 'w_is_primary',
                'title' => __('Primary'),
                'class' => 'input-select',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $baseFieldset->addField(
            'w_display_on_front',
            'select',
            [
                'name' => 'w_display_on_front',
                'label' => __('Display on front'),
                'id' => 'w_display_on_front',
                'title' => __('Display on front'),
                'class' => 'input-select',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $baseFieldset->addField(
            'w_website',
            'select',
            array(
                'name' => 'w_website',
                'label' => __('Website'),
                'options' => $this->_getWebsiteOptions()
            )
        );

        $baseFieldset->addField(
            'w_notes',
            'textarea',
            [
                'name' => 'w_notes',
                'label' => __('Notes'),
                'id' => 'w_notes',
                'title' => __('Notes'),
                'class' => '',
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'w_open_hours',
            'textarea',
            [
                'name' => 'w_open_hours',
                'label' => __('Open hours'),
                'id' => 'w_open_hours',
                'title' => __('Open hours'),
                'class' => '',
                'required' => false
            ]
        );


        $supplyneedsFieldset = $form->addFieldset('supplyneeds_fieldset', ['legend' => __('Supply needs')]);

        if ($this->_config->isSupplierIsInstalled()) {
            $supplyneedsFieldset->addField(
                'w_use_in_supplyneeds',
                'select',
                [
                    'name' => 'w_use_in_supplyneeds',
                    'label' => __('Use for supply needs'),
                    'id' => 'w_use_in_supplyneeds',
                    'title' => __('Use for supply needs'),
                    'class' => 'input-select',
                    'options' => ['1' => __('Yes'), '0' => __('No')]
                ]
            );
        }

        $supplyneedsFieldset->addField(
            'w_default_warning_stock_level',
            'text',
            [
                'name' => 'w_default_warning_stock_level',
                'label' => __('Default warning stock level'),
                'id' => 'w_default_warning_stock_level',
                'title' => __('Default warning stock level'),
                'class' => '',
                'required' => false
            ]
        );

        $supplyneedsFieldset->addField(
            'w_default_ideal_stock_level',
            'text',
            [
                'name' => 'w_default_ideal_stock_level',
                'label' => __('Default ideal stock level'),
                'id' => 'w_default_ideal_stock_level',
                'title' => __('Default ideal stock level'),
                'class' => '',
                'required' => false
            ]
        );

        $automaticCalculationFieldset = $form->addFieldset('supplyneeds_automatic_calculation', ['legend' => __('Automatic warning & ideal levels calculation')]);

        $automaticCalculationFieldset->addField(
            'w_enable_lowstock_update',
            'select',
            [
                'name' => 'w_enable_lowstock_update',
                'label' => __('Enable automatic update'),
                'id' => 'w_enable_lowstock_update',
                'title' => __('Enable automatic update'),
                'class' => '',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $automaticCalculationFieldset->addField(
            'w_lowstock_optimal',
            'text',
            [
                'name' => 'w_lowstock_optimal',
                'label' => __('Optimal stock level duration'),
                'id' => 'w_lowstock_optimal',
                'title' => __('Optimal stock level duration'),
                'class' => '',
                'note' => __('In days, default value is 15')
            ]
        );

        $automaticCalculationFieldset->addField(
            'w_lowstock_warning_percentage',
            'text',
            [
                'name' => 'w_lowstock_warning_percentage',
                'label' => __('Warning stock %'),
                'id' => 'w_lowstock_warning_percentage',
                'title' => __('Warning stock %'),
                'class' => '',
                'note' => __('Warning stock level automatically set to <b>X%</b> of the ideal stock level. Default value is 30%')
            ]
        );

        $automaticCalculationFieldset->addField(
            'w_ignore_sales_below_1',
            'select',
            [
                'name' => 'w_ignore_sales_below_1',
                'label' => __('Ignore statistics lower than 1'),
                'id' => 'w_ignore_sales_below_1',
                'title' => __('Ignore statistics lower than 1'),
                'class' => '',
                'options' => ['0' => __('No'), '1' => __('Yes')],
                'note' => __('If enabled, warning & ideal stock levels will be set to 0 when calculation is below 1')
            ]
        );

        $addressFieldset = $form->addFieldset('address_fieldset', ['legend' => __('Address')]);

        $addressFieldset->addField(
            'w_company_name',
            'text',
            [
                'name' => 'w_company_name',
                'label' => __('Company name'),
                'id' => 'w_company_name',
                'title' => __('Company name'),
                'class' => '',
                'required' => false
            ]
        );

        $addressFieldset->addField(
            'w_street1',
            'text',
            [
                'name' => 'w_street1',
                'label' => __('Street 1'),
                'id' => 'w_street1',
                'title' => __('Street 1'),
                'class' => '',
                'required' => false
            ]
        );


        $addressFieldset->addField(
            'w_street2',
            'text',
            [
                'name' => 'w_street2',
                'label' => __('Street 2'),
                'id' => 'w_street2',
                'title' => __('Street 2'),
                'class' => '',
                'required' => false
            ]
        );

        $addressFieldset->addField(
            'w_postcode',
            'text',
            [
                'name' => 'w_postcode',
                'label' => __('Postcode'),
                'id' => 'w_postcode',
                'title' => __('Postcode'),
                'class' => '',
                'required' => false
            ]
        );

        $addressFieldset->addField(
            'w_city',
            'text',
            [
                'name' => 'w_city',
                'label' => __('City'),
                'id' => 'w_city',
                'title' => __('City'),
                'class' => '',
                'required' => false
            ]
        );

        $addressFieldset->addField(
            'w_state',
            'text',
            [
                'name' => 'w_state',
                'label' => __('State / Region'),
                'id' => 'w_state',
                'title' => __('State / Region'),
                'class' => '',
                'required' => false
            ]
        );

        $addressFieldset->addField(
            'w_country',
            'select',
            [
                'name' => 'w_country',
                'label' => __('Country'),
                'title' => __('Country'),
                'values' => $this->_countryLists->create()->toOptionArray(),
                'class' => 'select'
            ]
        );

        $addressFieldset->addField(
            'w_telephone',
            'text',
            [
                'name' => 'w_telephone',
                'label' => __('Phone'),
                'id' => 'w_telephone',
                'title' => __('Phone'),
                'class' => '',
                'required' => false
            ]
        );

        $addressFieldset->addField(
            'w_fax',
            'text',
            [
                'name' => 'w_fax',
                'label' => __('Fax'),
                'id' => 'w_fax',
                'title' => __('Fax'),
                'class' => '',
                'required' => false
            ]
        );

        $poSynchronizeFieldset = $form->addFieldset('po_synchronization_fieldset', ['legend' => __('Purchase order synchronization')]);

        if ($this->_config->isSupplierIsInstalled()) {
            $poSynchronizeFieldset->addField(
                'w_sync_stock_from_po',
                'select',
                array(
                    'name' => 'w_sync_stock_from_po',
                    'label' => __('Enable'),
                    'title' => __('Enable'),
                    'class' => 'input-select',
                    'options' => ['1' => __('Yes'), '0' => __('No')],
                    'note' => __('If enabled, quantity expected from purchase order will be synced to this warehouse')
                )
            );
        }

        $this->_eventManager->dispatch('bms_advancedstock_warehouse_edit_main_prepare_form', ['warehouse' => $model, 'form' => $form]);

        $data = $model->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getWebsiteOptions()
    {
        $options = [];
        foreach ($this->_websiteCollectionFactory->create() as $item) {
            $options[$item->getId()] = $item->getname();
        }
        return $options;
    }

}

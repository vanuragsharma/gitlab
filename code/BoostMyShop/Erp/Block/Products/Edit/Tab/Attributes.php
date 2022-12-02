<?php

namespace BoostMyShop\Erp\Block\Products\Edit\Tab;

class Attributes extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_advancedStockConfig;
    protected $_orderPreparationConfig;
    protected $_eventManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \BoostMyShop\AdvancedStock\Model\Config $advancedStockConfig,
        \BoostMyShop\OrderPreparation\Model\Config $orderPreparationConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $data = []
    ) {

        parent::__construct($context, $registry, $formFactory, $data);

        $this->_advancedStockConfig = $advancedStockConfig;
        $this->_orderPreparationConfig = $orderPreparationConfig;
        $this->_eventManager = $eventManager;
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    protected function _prepareForm()
    {

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('supplier_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Attributes')]);

        $attributes = $this->getAttributes();

        foreach($attributes as $attribute)
        {
            $baseFieldset->addField(
                $attribute['code'],
                $attribute['type'],
                [
                    'name' => 'attributes['.$attribute['code'].']',
                    'label' => __($attribute['label']),
                    'id' => $attribute['code'],
                    'title' => __($attribute['label']),
                    'value' => $attribute['value'],
                    'options' => (isset($attribute['options']) ? $attribute['options'] : []),
                ]
            );
        }

        $this->_eventManager->dispatch('bms_erp_product_view_attribute_fieldset', ['form' => $form, 'product' => $this->getProduct()]);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getAttributes()
    {
        $attributes = [];

        //barcode
        if ($this->_advancedStockConfig->getBarcodeAttribute()) {
            $attributes[] = [
                                'code' => $this->_advancedStockConfig->getBarcodeAttribute(),
                                'label' => __('Barcode'),
                                'type' => 'text',
                                'value' => $this->getProduct()->getData($this->_advancedStockConfig->getBarcodeAttribute()),
                                'options' => []
            ];
        }

        //MPN
        if ($this->_orderPreparationConfig->getMpnAttribute())
        {
            $attributes[] = [
                'code' => $this->_orderPreparationConfig->getMpnAttribute(),
                'label' => __('MPN'),
                'type' => 'text',
                'value' => $this->getProduct()->getData($this->_orderPreparationConfig->getMpnAttribute()),
                'options' => []
            ];
        }

        //Weight
        $attributes[] = [
            'code' => 'weight',
            'label' => __('Weight'),
            'type' => 'text',
            'value' => $this->getProduct()->getData('weight'),
            'options' => []
        ];

        //volume
        if ($this->_orderPreparationConfig->getVolumeAttribute())
        {
            $attributes[] = [
                'code' => $this->_orderPreparationConfig->getVolumeAttribute(),
                'label' => __('Volume'),
                'type' => 'text',
                'value' => $this->getProduct()->getData($this->_orderPreparationConfig->getVolumeAttribute()),
                'options' => []
            ];
        }

        //Package number
        if ($this->_orderPreparationConfig->getPackageNumberAttribute()) {
            $attributes[] = [
                'code' => $this->_orderPreparationConfig->getPackageNumberAttribute(),
                'label' => __('Package number'),
                'type' => 'text',
                'value' => $this->getProduct()->getData($this->_orderPreparationConfig->getPackageNumberAttribute()),
                'options' => []
            ];
        }

        $attributes[] = [
            'code' => 'supply_discontinued',
            'label' => __('Discontinued'),
            'type' => 'select',
            'value' => $this->getProduct()->getData('supply_discontinued'),
            'options' => ['1' => __('Yes'), '0' => __('No')]
        ];

        //raise event to allow other module to add new attributes
        $obj = new \Magento\Framework\DataObject();
        $obj->setAttributes($attributes);
        $this->_eventManager->dispatch('bms_erp_product_view_attributes', ['obj' => $obj, 'product' => $this->getProduct()]);
        $attributes = $obj->getAttributes();

        return $attributes;
    }

    public function getTabLabel()
    {
        return __('Attributes');
    }

    public function getTabTitle()
    {
        return __('Attributes');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

}

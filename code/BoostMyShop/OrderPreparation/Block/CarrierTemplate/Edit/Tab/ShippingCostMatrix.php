<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab;

class ShippingCostMatrix extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_carrierList;
    protected $_templateType;
    protected $_websiteType;
    protected $_warehouseList;

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
        \BoostMyShop\OrderPreparation\Model\Config\Source\Warehouses $warehouseList,
        array $data = []
    ) {
        $this->_carrierList  = $carrierList;
        $this->_templateType  = $templateType;
        $this->_websiteType  = $websiteType;
        $this->_warehouseList  = $warehouseList;

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

        $shippingCostFieldset = $form->addFieldset('shippingcost_fieldset', ['legend' => __('Shipping cost matrix')]);

        $rendererShipping = $this->_layout->createBlock('BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\Renderer\ShippingCostMapping');
        $rendererShipping->setModel($model);
        $shippingCostFieldset->setRenderer($rendererShipping);

        $data = $model->getData();

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}

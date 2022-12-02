<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\Renderer;

class Ups extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_countryFactory;

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
        \Magento\Directory\Model\Config\Source\Country $countryFactory,
        array $data = []
    ) {
        $this->_countryFactory = $countryFactory;
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

        $baseFieldset = $form->addFieldset('ups_fieldset', ['legend' => __('UPS settings')]);

        $baseFieldset->addField(
            'ct_custom_user_id',
            'text',
            [
                'name' => 'ct_custom[user_id]',
                'label' => __('User id'),
                'id' => 'ct_custom_user_id',
                'title' => __('User id'),
                'required' => false,
                'value' => $model->getCustomValue('user_id')
            ]
        );

        $baseFieldset->addField(
            'ct_custom_password',
            'text',
            [
                'name' => 'ct_custom[password]',
                'label' => __('Password'),
                'id' => 'ct_custom_password',
                'title' => __('Password'),
                'required' => false,
                'value' => $model->getCustomValue('password')
            ]
        );

        $baseFieldset->addField(
            'ct_custom_access_key',
            'text',
            [
                'name' => 'ct_custom[access_key]',
                'label' => __('Access key'),
                'id' => 'ct_custom_access_key',
                'title' => __('Access key'),
                'required' => false,
                'value' => $model->getCustomValue('access_key')
            ]
        );

        $baseFieldset->addField(
            'ct_custom_shipper_number',
            'text',
            [
                'name' => 'ct_custom[shipper_number]',
                'label' => __('Shipper number'),
                'id' => 'ct_custom_shipper_number',
                'title' => __('Shipper number'),
                'required' => false,
                'value' => $model->getCustomValue('shipper_number')
            ]
        );

        $baseFieldset->addField(
            'ct_custom_test_mode',
            'select',
            [
                'name' => 'ct_custom[test_mode]',
                'label' => __('Test mode'),
                'id' => 'ct_custom_test_mode',
                'title' => __('Test mode'),
                'required' => false,
                'options' => ['0' => __('No'), '1' => __('Yes')],
                'value' => $model->getCustomValue('test_mode')
            ]
        );

        $senderFieldset = $form->addFieldset('sender_fieldset', ['legend' => __('Sender settings')]);

        $senderFieldset->addField(
            'ct_custom_company',
            'text',
            [
                'name' => 'ct_custom[company]',
                'label' => __('Company'),
                'id' => 'ct_custom_company',
                'title' => __('Company'),
                'required' => false,
                'value' => $model->getCustomValue('company')
            ]
        );

        $senderFieldset->addField(
            'ct_custom_street',
            'text',
            [
                'name' => 'ct_custom[street]',
                'label' => __('Street'),
                'id' => 'ct_custom_street',
                'title' => __('Street'),
                'required' => false,
                'value' => $model->getCustomValue('street')
            ]
        );

        $senderFieldset->addField(
            'ct_custom_city',
            'text',
            [
                'name' => 'ct_custom[city]',
                'label' => __('City'),
                'id' => 'ct_custom_city',
                'title' => __('City'),
                'required' => false,
                'value' => $model->getCustomValue('city')
            ]
        );

        $senderFieldset->addField(
            'ct_custom_regioncode',
            'text',
            [
                'name' => 'ct_custom[regioncode]',
                'label' => __('State'),
                'id' => 'ct_custom_regioncode',
                'title' => __('State'),
                'required' => false,
                'value' => $model->getCustomValue('regioncode')
            ]
        );

        $senderFieldset->addField(
            'ct_custom_postcode',
            'text',
            [
                'name' => 'ct_custom[postcode]',
                'label' => __('Postcode'),
                'id' => 'ct_custom_postcode',
                'title' => __('Postcode'),
                'required' => false,
                'value' => $model->getCustomValue('postcode')
            ]
        );

        $senderFieldset->addField(
            'ct_custom_country',
            'select',
            [
                'name' => 'ct_custom[country]',
                'label' => __('Country code'),
                'id' => 'ct_custom_country',
                'title' => __('Country code'),
                'required' => false,
                'options' => $this->countryOptionArray(),
                'value' => $model->getCustomValue('country')
            ]
        );

        $senderFieldset->addField(
            'ct_custom_phone',
            'text',
            [
                'name' => 'ct_custom[phone]',
                'label' => __('Phone'),
                'id' => 'ct_custom_phone',
                'title' => __('Phone'),
                'required' => false,
                'value' => $model->getCustomValue('phone')
            ]
        );

        $senderFieldset->addField(
            'ct_custom_email',
            'text',
            [
                'name' => 'ct_custom[email]',
                'label' => __('Email'),
                'id' => 'ct_custom_email',
                'title' => __('Email'),
                'required' => false,
                'value' => $model->getCustomValue('email')
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function countryOptionArray()
    {
        $options = array();
        $collection = $this->_countryFactory->toOptionArray();
        foreach($collection as $country)
            $options[$country['value']] = $country['label'];

        return $options;
    }

}

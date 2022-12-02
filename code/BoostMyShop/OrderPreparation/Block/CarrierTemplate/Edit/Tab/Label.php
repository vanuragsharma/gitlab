<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab;

class Label extends \Magento\Backend\Block\Widget\Form\Generic
{

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
        array $data = []
    ) {
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

        $baseFieldset = $form->addFieldset('label_fieldset', ['legend' => __('Label Configuration')]);

        $baseFieldset->addField(
            'ct_custom_label_configuration',
            'textarea',
            [
                'name' => 'ct_custom[label_configuration]',
                'label' => __('Label content'),
                'id' => 'ct_custom_label_configuration',
                'title' => __('Label content'),
                'required' => false,
                'value' => $model->getCustomValue('label_configuration'),
                'note' => __('You can use codes {firstname} {lastname} {company} {street1} {street2} {city} {postcode} {region} {country} {telephone}')
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();

    }

}

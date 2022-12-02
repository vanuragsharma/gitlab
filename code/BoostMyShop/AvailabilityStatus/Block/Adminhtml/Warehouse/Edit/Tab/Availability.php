<?php

namespace BoostMyShop\AvailabilityStatus\Block\Adminhtml\Warehouse\Edit\Tab;

class Availability extends \Magento\Backend\Block\Widget\Form\Generic
{


    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_warehouse');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('warehouse_');

        $baseFieldset = $form->addFieldset('availability_fieldset', ['legend' => __('Availability status')]);

        $baseFieldset->addField(
            'w_availability_delay',
            'text',
            [
                'name' => 'w_availability_delay',
                'label' => __('Delay'),
                'id' => 'w_availability_delay',
                'title' => __('Delay'),
                'note' => __('Delay to ship an order when product is in this warehouse')
            ]
        );

        $data = $model->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}

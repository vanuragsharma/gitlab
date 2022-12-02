<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab;

class Export extends \Magento\Backend\Block\Widget\Form\Generic
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

        $baseFieldset = $form->addFieldset('export_fieldset', ['legend' => __('Export')]);

        $baseFieldset->addField(
            'ct_export_file_name',
            'text',
            [
                'name' => 'ct_export_file_name',
                'label' => __('File name'),
                'id' => 'ct_export_file_name',
                'title' => __('File name'),
                'required' => true,
                'note' => __('You can include {increment_id} in the file name to inject the shipment reference')
            ]
        );

        $baseFieldset->addField(
            'ct_export_file_mime',
            'select',
            [
                'name' => 'ct_export_file_mime',
                'label' => __('Mime type'),
                'id' => 'ct_export_file_mime',
                'title' => __('Mime type'),
                'class' => 'input-select',
                'options' => $this->getMimeTypes()
            ]
        );

        $baseFieldset->addField(
            'ct_export_encoding',
            'select',
            [
                'name' => 'ct_export_encoding',
                'label' => __('Encoding'),
                'id' => 'ct_export_encoding',
                'title' => __('Encoding'),
                'class' => 'input-select',
                'options' => $this->getEncodingTypes()
            ]
        );

        $baseFieldset->addField(
            'ct_export_order_filter',
            'select',
            [
                'name' => 'ct_export_order_filter',
                'label' => __('Orders to export'),
                'id' => 'ct_export_order_filter',
                'title' => __('Orders to export'),
                'class' => 'input-select',
                'options' => ['0' => __('Only packed orders'), '1' => __('All orders')]
            ]
        );

        if ($model->getct_type() == \BoostMyShop\OrderPreparation\Model\CarrierTemplate::kTypeOrderDetailsExport)
        {
            $baseFieldset->addField(
                'ct_export_remove_accents',
                'select',
                [
                    'name' => 'ct_export_remove_accents',
                    'label' => __('Remove accents'),
                    'id' => 'ct_export_remove_accents',
                    'title' => __('Remove accents'),
                    'class' => 'input-select',
                    'options' => ['0' => __('No'), '1' => __('Yes')]
                ]
            );

            $baseFieldset->addField(
                'ct_export_line_break',
                'select',
                [
                    'name' => 'ct_export_line_break',
                    'label' => __('Line break'),
                    'id' => 'ct_export_line_break',
                    'title' => __('Line break'),
                    'class' => 'input-select',
                    'options' => ['cr' => __('CR (Carriage Return)'), 'lf' => __('LF (Line Feed)'), 'cr_lf' => __('CR+LF (Carriage Return + Line Feed)')]
                ]
            );

            $baseFieldset->addField(
                'ct_export_file_header',
                'textarea',
                [
                    'name' => 'ct_export_file_header',
                    'label' => __('File header line'),
                    'id' => 'ct_export_file_header',
                    'title' => __('File header line')
                ]
            );

            $baseFieldset->addField(
                'ct_export_file_order_header',
                'textarea',
                [
                    'name' => 'ct_export_file_order_header',
                    'label' => __('Header line for orders'),
                    'id' => 'ct_export_file_order_header',
                    'title' => __('Header line for orders')
                ]
            );

            $baseFieldset->addField(
                'ct_export_file_order_products',
                'textarea',
                [
                    'name' => 'ct_export_file_order_products',
                    'label' => __('Line for each products in orders'),
                    'id' => 'ct_export_file_order_products',
                    'title' => __('Line for each products in orders')
                ]
            );

            $baseFieldset->addField(
                'ct_export_file_order_footer',
                'textarea',
                [
                    'name' => 'ct_export_file_order_footer',
                    'label' => __('Footer line for orders'),
                    'id' => 'ct_export_file_order_footer',
                    'title' => __('Footer line for orders')
                ]
            );

            $baseFieldset->addField(
                'ct_export_file_footer',
                'textarea',
                [
                    'name' => 'ct_export_file_footer',
                    'label' => __('File footer line'),
                    'id' => 'ct_export_file_footer',
                    'title' => __('File footer line')
                ]
            );
        }


        $data = $model->getData();


        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getMimeTypes()
    {
        $types = [];

        $types['text/plain'] = 'text, csv';
        $types['application/pdf'] = 'pdf';
        $types['text/xml'] = 'xml';
        $types['x-application/zpl'] = 'zpl';

        return $types;
    }

    protected function getEncodingTypes()
    {
        $EncodingTypes = [];
        $EncodingTypes['utf8'] = 'UTF-8';
        $EncodingTypes['ansi'] = 'ANSI';

        return $EncodingTypes;

    }

}

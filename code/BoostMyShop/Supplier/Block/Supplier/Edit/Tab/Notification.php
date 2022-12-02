<?php

namespace BoostMyShop\Supplier\Block\Supplier\Edit\Tab;

class Notification extends \Magento\Backend\Block\Widget\Form\Generic
{

    protected $_localeLists;
    protected $_date;
    protected $_statusList;

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
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \BoostMyShop\Supplier\Model\Order\Status $statusList,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->_localeLists = $localeLists;
        $this->_date = $date;
        $this->_statusList = $statusList;
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
        $model = $this->_coreRegistry->registry('current_supplier');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('supplier_');

        $baseFieldset = $form->addFieldset('notification_fieldset', ['legend' => __('Notification settings')]);

        $baseFieldset->addField(
            'sup_enable_notification',
            'select',
            [
                'name' => 'sup_enable_notification',
                'label' => __('Send email notification'),
                'class' => 'input-select',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $doNotChangeStatus = new \Magento\Framework\Phrase(__('Do not change'), []);
        $poStatuses = $this->_statusList->toOptionArray();
        array_unshift($poStatuses, $doNotChangeStatus);
        $baseFieldset->addField(
            'sup_po_notified_status',
            'select',
            [
                'name' => 'sup_po_notified_status',
                'label' => __('Status for notified PO'),
                'id' => 'sup_po_notified_status',
                'title' => __('Status for notified PO'),
                'values' => $poStatuses,
                'class' => 'select',
                'note'   => __('This status will be automatically assigned to the PO when its supplier has been notified.')
            ]
        );

        $baseFieldset->addField(
            'sup_attach_pdf',
            'select',
            [
                'name' => 'sup_attach_pdf',
                'label' => __('Attach PDF'),
                'class' => 'input-select',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $baseFieldset->addField(
            'sup_attach_file',
            'select',
            [
                'name' => 'sup_attach_file',
                'label' => __('Attach File'),
                'class' => 'input-select',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $baseFieldset->addField(
            'sup_notif_ftp_enabled',
            'select',
            [
                'name' => 'sup_notif_ftp_enabled',
                'label' => __('Upload file on ftp'),
                'class' => 'input-select',
                'options' => ['0' => __('No'), '1' => __('Yes')],
                'note' => __('Works only if send email notification is enabled')
            ]
        );

        $delayedFieldset = $form->addFieldset('delayed_notification_fieldset', ['legend' => __('Grouped notification')]);

        $delayedFieldset->addField(
            'sup_delayed_notification',
            'select',
            [
                'name' => 'sup_delayed_notification',
                'label' => __('Enable'),
                'class' => 'input-select',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $delayedFieldset->addType('multiplecheckbox', '\BoostMyShop\Supplier\Block\Supplier\Edit\Tab\Renderer\Hours');

        $delayedFieldset->addField(
            'sup_delayed_notification_hours',
            'multiplecheckbox',
            [
                'label' => __('Notification hours'),
                'class' => 'sup_delayed_notification_hours',
                'name' => 'sup_delayed_notification_hours',
            ]
        );

        $delayedFieldset->addField(
            'note_server_time',
            'label',
            [
                'label'         => __('Current server time'),
                'value'         => $this->_date->gmtDate("H")
            ]
        );

        $fileFieldset = $form->addFieldset('file_fieldset', ['legend' => __('File settings')]);

        $fileFieldset->addField(
            'sup_file_name',
            'text',
            [
                'name'  => 'sup_file_name',
                'label' => __('File name'),
                'required' => false,
                'note'  => __('Filename to download. To insert the purchase order number, insert {reference} code')
            ]
        );



        $fileFieldset->addField(
            'sup_file_header',
            'textarea',
            [
                'name' => 'sup_file_header',
                'label' => __('File header'),
                'required' => false,
                'note' => 'First line of the file'
            ]
        );

        $fileFieldset->addField(
            'sup_file_order_header',
            'textarea',
            [
                'name' => 'sup_file_order_header',
                'label' => __('Order header'),
                'required' => false,
                'note' => 'Use for XML export only'
            ]
        );


        $fileFieldset->addField(
            'sup_file_product',
            'textarea',
            [
                'name' => 'sup_file_product',
                'label' => __('Product line'),
                'required' => false,
                'note' => 'Repeated for every products in the purchase order'
            ]
        );

        $fileFieldset->addField(
            'sup_file_order_footer',
            'textarea',
            [
                'name' => 'sup_file_order_footer',
                'label' => __('Order footer'),
                'required' => false,
                'note' => 'Use for XML export only'
            ]
        );

        $fileFieldset->addField(
            'sup_file_footer',
            'textarea',
            [
                'name' => 'sup_file_footer',
                'label' => __('File footer'),
                'required' => false,
                'note' => 'Use for XML export only'
            ]
        );

        $ftpFieldset = $form->addFieldset('ftp_upload_fieldset', ['legend' => __('FTP upload')]);

        $ftpFieldset->addField(
            'sup_notif_ftp_sftp',
            'select',
            [
                'name' => 'sup_notif_ftp_sftp',
                'label' => __('SFTP'),
                'class' => 'input-select',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $ftpFieldset->addField(
            'sup_notif_ftp_passive',
            'select',
            [
                'name' => 'sup_notif_ftp_passive',
                'label' => __('Passive'),
                'class' => 'input-select',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $ftpFieldset->addField(
            'sup_notif_ftp_host',
            'text',
            [
                'name' => 'sup_notif_ftp_host',
                'label' => __('Host')
            ]
        );

        $ftpFieldset->addField(
            'sup_notif_ftp_port',
            'text',
            [
                'name' => 'sup_notif_ftp_port',
                'label' => __('Port')
            ]
        );

        $ftpFieldset->addField(
            'sup_notif_ftp_login',
            'text',
            [
                'name' => 'sup_notif_ftp_login',
                'label' => __('Login'),
            ]
        );

        $ftpFieldset->addField(
            'sup_notif_ftp_password',
            'password',
            [
                'name' => 'sup_notif_ftp_password',
                'label' => __('Password'),
            ]
        );

        $ftpFieldset->addField(
            'sup_notif_ftp_directory',
            'text',
            [
                'name' => 'sup_notif_ftp_directory',
                'label' => __('Directory'),
            ]
        );

        $ftpFieldset->addField(
            'sup_notif_ftp_file_name',
            'text',
            [
                'name' => 'sup_notif_ftp_file_name',
                'label' => __('File Name'),
                'note' => __('you can use codes {d} {m} {Y} {reference} as dynamic variable in the file name.')
            ]
        );

        $availableCodesFieldset = $form->addFieldset('available_codes_fieldset', ['legend' => __('Codes available')]);
        $availableCodesFieldset->addType('codes', '\BoostMyShop\Supplier\Block\Supplier\Edit\Tab\Renderer\Codes');

        $availableCodesFieldset->addField(
            'codes',
            'codes',
            [
                'label' => __('Codes'),
            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Element\Dependence::class
            )
                ->addFieldMap(
                    "supplier_sup_delayed_notification",
                    'sup_delayed_notification'
                )
                ->addFieldMap(
                    "supplier_sup_delayed_notification_hours_table",
                    'sup_delayed_notification_hours'
                )
                ->addFieldDependence(
                    'sup_delayed_notification_hours',
                    "sup_delayed_notification",
                    '1'
                )
        );

        $data = $model->getData();
        $data['note_server_time'] = $this->_date->gmtDate("H");
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}

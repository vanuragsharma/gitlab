<?php
namespace BoostMyShop\Supplier\Block\Order\Receive;

class ImportReception extends \Magento\Backend\Block\Template
{
    protected $_template = 'Order/Receive/ImportReception.phtml';
    protected $_coreRegistry = null;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, array $data = [])
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getPoId()
    {
        return $this->_coreRegistry->registry('current_purchase_order')->getId();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/ProcessImportReception', ['po_id' => $this->getPoId()]);
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/receive', ['po_id' => $this->_coreRegistry->registry('current_purchase_order')->getId()]);
    }

    public function getColumnsHtml()
    {
        $html = '<li>sku <b>(mandatory)</b></li>
                 <li>qty (1 by default)</li>
                 <li>qty_pack (1 by default)</li>';

        $obj = new \Magento\Framework\DataObject();
        $obj->setHtml($html);
        $this->_eventManager->dispatch('bms_supplier_purchase_order_reception_import_columns', ['obj' => $obj]);
        $html = $obj->getHtml();

        return $html;
    }
}
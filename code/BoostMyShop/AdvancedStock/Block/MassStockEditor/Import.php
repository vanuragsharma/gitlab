<?php
namespace BoostMyShop\AdvancedStock\Block\MassStockEditor;

class Import extends \Magento\Backend\Block\Template
{
    protected $_template = 'MassStockEditor/Import.phtml';

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/ProcessImport');
    }


}
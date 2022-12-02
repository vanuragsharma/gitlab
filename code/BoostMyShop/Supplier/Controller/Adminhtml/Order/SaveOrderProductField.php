<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

use Magento\Framework\Controller\ResultFactory;

class SaveOrderProductField extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    /**
     * @return void
     */
    public function execute()
    {
        $result = ['success' => true, 'message' => ''];

        try
        {
            $data = $this->getRequest()->getPostValue();

            if ($data['field'] == 'pop_eta')
                $data = $this->_filterPostData($data);


            $popId = (int)$data['pop_id'];
            $field = $data['field'];
            $value = $data['value'];

            $pop = $this->_orderProductFactory->create()->load($popId);
            if($pop->getData($field) != $value)
            {
                $order = $this->_orderFactory->create()->load($pop->getpop_po_id());
                $order->addHistory(__('%1 of product %2 updated from %3 to %4', $this->getFieldLabel($field), $this->getProductSku($pop['pop_sku']), $pop->getData($field), $value));
                $pop->setData($field, $value)->save();
            }


            $result['success'] = true;
        }
        catch(\Exception $ex)
        {
            $result['success'] = false;
            $result['message'] = $ex->getMessage();
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }

    protected function getProductSku($sku)
    {
        return $sku;
    }

    protected function getFieldLabel($field)
    {
       switch ($field)
       {
           case 'pop_price':
               return __('Buying price');
               break;
           case 'pop_discount_percent':
               return __('Discount');
               break;
           case 'pop_tax_rate':
               return __('Tax rate');
               break;
           case 'pop_qty_pack':
               return __('Pack qty');
               break;
           case 'pop_qty':
               return __('Qty');
               break;
           case 'pop_eta':
               return __('ETA');
               break;
           case 'pop_supplier_sku':
               return __('Supplier sku');
               break;
           default:
               return $field;
               break;
       }
    }

    protected function _filterPostData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            ['value' => $this->_dateFilter],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        return $data;
    }

}

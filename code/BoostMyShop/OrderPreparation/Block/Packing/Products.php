<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

class Products extends AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/Products.phtml';

    protected function _prepareLayout()
    {
        if($this->_config->getLargeOrderMode($this->getCurrentWebsiteId()))
            $this->setTemplate('OrderPreparation/Packing/LargeProducts.phtml');

        parent::_prepareLayout();
        return $this;
    }

    public function getProducts()
    {
        return $this->currentOrderInProgress()->getAllItems();
    }

    public function getProductLocation($productId)
    {
        return $this->_product->create()->getLocation($productId, $this->_preparationRegistry->getCurrentWarehouseId());
    }

    public function getProductImageUrl($productId)
    {
        return $this->_product->create()->getImageUrl($productId);
    }

    public function getBarcode($productId)
    {
        return $this->_product->create()->getBarcode($productId);
    }

    public function getMpn($productId)
    {
        return $this->_product->create()->getMpn($productId);
    }

    public function getAdditionalBarcodes($productId)
    {
        return $this->_product->create()->getAdditionalBarcodes($productId);
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/confirmPacking');
    }

    public function getEditOrderItemUrl($item)
    {
        return $this->getUrl('*/*/editItem', ['item_id' => $item->getId()]);
    }

    public function isOrderEditorEnabled()
    {
        return $this->_config->isOrderEditorEnabled();
    }

    public function getConfigurableOptionsAsText($item)
    {
        $txt = array();

        if ($item->getOrderItem()->getparent_item_id()) {
            $parentItem = $this->_orderItemFactory->create()->load($item->getOrderItem()->getparent_item_id());
            $options = $parentItem->getProductOptions();
            if (isset($options['attributes_info']) && is_array($options['attributes_info']))
            {
                foreach($options['attributes_info'] as $info)
                {
                    $txt[] = $info['label'].': '.$info['value'];
                }
            }
        }

        return implode('<br>', $txt);
    }

    public function getProductOptions($item)
    {
        $txt = [];
        $options = $item->getOrderItem()->getProductOptions();

        if (isset($options['options']) && is_array($options['options']) && count($options['options']) > 0) {
            foreach($options['options'] as $option) {
                if (isset($option['label'])) {
                    if(isset($option['print_value'])) {
                        $txt[] = '<b>'.$option['label'].'</b> : '.$option['print_value'];
                    } elseif (isset($option['value'])) {
                        $txt[] = '<b>'.$option['label'].'</b> : '.$option['value'];
                    }
                }
            }
        } else {
            //try with parent
            if ($item->getOrderItem()->getparent_item_id()) {
                $parentItem = $this->_orderItemFactory->create()->load($item->getOrderItem()->getparent_item_id());
                $options = $parentItem->getProductOptions();
                if (isset($options['options']) && is_array($options['options']) && count($options['options']) > 0) {
                    foreach($options['options'] as $option) {
                        if (isset($option['label'])) {
                            if (isset($option['print_value'])) {
                                $txt[] = '<b>'.$option['label'].'</b> : '.$option['print_value'];
                            } elseif (isset($option['value'])) {
                                $txt[] = '<b>'.$option['label'].'</b> : '.$option['value'];
                            }
                        }
                    }
                }
            } else {
                return false;
            }
        }

        return implode('<br>', $txt);
    }

    public function showGlobalQtyButtons()
    {
        $resourceId = 'BoostMyShop_OrderPreparation::packing_show_global_qty_buttons';
        return $this->_policyAuth->isAllowed($resourceId);
    }

    public function showQtyButtons()
    {
        $resourceId = 'BoostMyShop_OrderPreparation::packing_show_qty_buttons';
        return $this->_policyAuth->isAllowed($resourceId);
    }

    public function getAdditionalHtmlInProductsForm()
    {
        $obj = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch('bms_orderpreparation_packing_in_products_form', ['order_in_progress' => $this->currentOrderInProgress(), 'obj' => $obj]);
        return $obj->getHtml();
    }

    public function getAdditionnalHtml()
    {
        $obj = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch('bms_orderpreparation_packing_after_products_block', ['order_in_progress' => $this->currentOrderInProgress(), 'obj' => $obj]);
        return $obj->getHtml();
    }

    public function isMutliboxAllow(){
        $template = $this->_carrierTemplateHelper->getCarrierTemplateForOrder($this->currentOrderInProgress(), $this->_preparationRegistry->getCurrentWarehouseId());
        if($template){
            $render = $template->getRenderer();
            if($render)
                return $render->supportMultiboxes();
        }
        return false;
    }

}
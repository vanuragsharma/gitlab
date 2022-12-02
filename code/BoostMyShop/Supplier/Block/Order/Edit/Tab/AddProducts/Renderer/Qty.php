<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer;

/**
 * Renderer for Qty field in sales create new order search grid
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Qty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    protected $_coreRegistry = null;
    protected $_supplierProductFactory = null;
    protected $_productFactory;
    protected $_config = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\Supplier\Model\Supplier\ProductFactory $supplierProductFactory,
                                \BoostMyShop\Supplier\Model\Product $productFactory,
                                \Magento\Framework\Registry $coreRegistry,
                                \BoostMyShop\Supplier\Model\Config $config,
                                array $data = [])
    {

        parent::__construct($context, $data);

        $this->_supplierProductFactory = $supplierProductFactory;
        $this->_productFactory = $productFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;
    }

    /**
     * Render product qty field
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $productId = $row->getId();
        $supplierId = $this->getOrder()->getpo_sup_id();
        $productSupplier = $this->_supplierProductFactory->create()->loadByProductSupplier($productId, $supplierId);

        //Add qty textbox
        $moq = $productSupplier->getsp_moq();

        $qty = $moq > 0 ? $moq : '1';
        $addClass = ' input-active';

        $buyingPrice = 'undefined';

        //get correct buying price
        if ($this->_config->getSetting('order_product/default_price') == 'product_supplier_association') {
            $buyingPrice = $productSupplier->getsp_price() ?: 'undefined';
        }
        if ($this->_config->getSetting('order_product/default_price') == 'product_cost') {
            $cost = $this->_productFactory->getCost($productId);
            $buyingPrice = $cost ?: 'undefined';
        }

        $packQtyOption = $this->_config->getSetting('general/pack_quantity');
        $packQty = ($productSupplier->getId() && $productSupplier->getsp_pack_qty()) ? $productSupplier->getsp_pack_qty() : 1;

        // Compose html
        $name = "qty_".$row->getId();
        $html = '<input type="text" ';
        $html .= 'id="' . $name . '" ';
        $html .= 'value="' . $qty . '" ';
        $html .= 'style="text-align: center" ';
        $html .= 'class="input-text admin__control-text ' . $this->getColumn()->getInlineCss() . $addClass . '" />';

        if($packQtyOption && $productSupplier->getId() && $productSupplier->getsp_pack_qty() && $productSupplier->getsp_pack_qty() > 1){
            $html.= '<br/><b>x ' . $productSupplier->getsp_pack_qty() . '</b><span>';
        }

        $html .= '<br/>';

        //show MOQ (if exists)
        if ($moq > 0)
            $html .= '<font color="red">MOQ: '.$moq.'</font>';

        $html .= '<br/>';

        // Add button
        $name = "select_".$row->getId();

        $html .= '<input type="button"';
        $html .= 'name="' . $name . '" ';
        $html .= 'id="' . $name . '" ';
        $html .= 'title="Select" ';
        $html .= 'class="action-default scalable" ';
        $html .= 'onclick="order.addProduct('.$row->getId().', '.$buyingPrice.', \''. base64_encode($this->getProductSku($row)).'\', \''.base64_encode($row->getname()).'\', '.$packQtyOption.', '.$packQty.'); return false;"';
        $html .= 'value="'.__('Add').'" ';
        $html .= '>';

        return $html;
    }

    protected function getProductSku($row)
    {
        return $row->getsku();
    }

    protected function getOrder()
    {
        return $this->_coreRegistry->registry('current_purchase_order');
    }
}

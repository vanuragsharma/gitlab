<?php

namespace Yalla\Vendors\Block\Adminhtml\Vendors\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Products extends Template implements TabInterface {

    /**
     * @param Context $context
     * 
     * @param array $data
     */
    public function __construct(
    \Magento\Backend\Block\Template\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
    }
    
    protected function _construct()
    {
        parent::_construct();
        //$childBlock = $this->getLayout()->createBlock('Yalla\Vendors\Block\Adminhtml\Vendors\Edit\Tab\ProductsGrid');
        //$this->addChild('vendor-products-grid', 'Yalla\Vendors\Block\Adminhtml\Vendors\Edit\Tab\ProductsGrid');
        $this->setTemplate('Yalla_Vendors::products.phtml');
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        
        return parent::_toHtml();
    }
    
    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel() {
        return __('Vendor Products');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return __('Vendor Products');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab() {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden() {
        return false;
    }

}

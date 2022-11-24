<?php

namespace Yalla\Vendors\Block\Adminhtml\Vendors\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendors_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Account Information'));
    }
    
    protected function _beforeToHtml()
    {
       	//other tabs 
		$products = $this->getLayout()->createBlock('Yalla\Vendors\Block\Adminhtml\Vendors\Edit\Tab\Products');

        $this->addTab(
            'address_info',
            [
                'label' => __('Products'),
                'title' => __('Products'),
                'content' => $products->toHtml(),
            ]
        );
  
        return parent::_beforeToHtml();
    }

}

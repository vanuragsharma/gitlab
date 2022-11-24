<?php
namespace Yalla\Vendors\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Orderitems extends Container
{

	protected function _construct()
	{
		$this->_controller = 'adminhtml_orderitems';
		$this->_blockGroup = 'Yalla_Vendors';
		$this->_headerText = __('Send Order Item');
		parent::_construct();
		
		$this->buttonList->remove('add');
	}
}

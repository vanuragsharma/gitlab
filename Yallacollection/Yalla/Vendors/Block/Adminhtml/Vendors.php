<?php
namespace Yalla\Vendors\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Vendors extends Container
{

	protected function _construct()
	{
		$this->_controller = 'adminhtml_vendors';
		$this->_blockGroup = 'Yalla_Vendors';
		$this->_headerText = __('Manage Vendors');
		$this->_addButtonLabel = __('Add New Vendors');
		parent::_construct();
	}
}
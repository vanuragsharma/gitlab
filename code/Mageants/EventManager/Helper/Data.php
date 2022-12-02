<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Helper;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper

{	
	/**
     * Get Store code
     *
     * @return string
     */
    public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
	}
	/**
     *
     *
     * @return bool
     */
	public function isEnable()
	{
		return $this->getConfigValue('event/general/EnableModule');
	}

	
	
}
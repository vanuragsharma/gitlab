<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Model;
use Magento\Framework\Exception\LocalizedException as CoreException;

class Eventdata extends \Magento\Framework\Model\AbstractModel
{
	
  	protected function _construct()
    {
        $this->_init('Mageants\EventManager\Model\ResourceModel\Eventdata');
    }

    

    
}
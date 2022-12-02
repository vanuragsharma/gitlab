<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * pgeneral ResourceModel class
 */ 
class Eventdata extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

	

	/**
	 * Init resource Model
	 */
    protected function _construct()
    {
        $this->_init('event_data', 'e_id');
    }

    



}
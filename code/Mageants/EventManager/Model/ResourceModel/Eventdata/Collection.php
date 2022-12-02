<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Model\ResourceModel\Eventdata;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
     * @var string
     */
    protected $_idFieldName = 'e_id';
    /**
     * Define resource model.
     */
  	protected function _construct()
    {
        $this->_init('Mageants\EventManager\Model\Eventdata', 'Mageants\EventManager\Model\ResourceModel\Eventdata');
    }
}
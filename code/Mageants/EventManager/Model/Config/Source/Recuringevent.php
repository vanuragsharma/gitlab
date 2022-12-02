<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Model\Config\Source;

/**
 * Stores Class return array
 */ 
class Recuringevent extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return Array
     */
    public function getAllOptions()
    {
       return [
            ['value' => '0', 'label' => __('Disbled')],
            ['value' => '1', 'label' => __('Enabled')],
        ];
    }
}
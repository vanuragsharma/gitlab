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
class Selectcolor extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return Array
     */
    public function getAllOptions()
    {
       return [

            ['value' => '', 'label' => __('--SELECT--')],
            ['value' => 'Red', 'label' => __('Red')],
            ['value' => 'Blue', 'label' => __('Blue')],
            ['value' => 'Green', 'label' => __('Green')],
            ['value' => 'Purple', 'label' => __('Purple')],
            ['value' => 'Orange', 'label' => __('Orange')],
            /*['value' => 'Yellow', 'label' => __('Yellow')],*/
            ['value' => 'Brown', 'label' => __('Brown')],
        ];
    }
}
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
class Repeatevent extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return Array
     */
    public function getAllOptions()
    {
       return [
            ['value' => '0', 'label' => __('--SELECT--')],
            ['value' => '1', 'label' => __('Weekly')],
            ['value' => '2', 'label' => __('Monthly')],
            ['value' => '3', 'label' => __('Yearly')],
        ];
    }
}
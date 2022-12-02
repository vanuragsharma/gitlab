<?php
/**
 * @category Mageants ProductLabel
 * @package Mageants_ProductLabel
 * @copyright Copyright (c) 2017 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\EventManager\Model\Config\Source;

/**
 * Stores Class return array
 */ 
class Repeatevery extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return Array
     */
    public function getAllOptions()
    {
       return [
            ['value' => '0', 'label' => __('--SELECT--')],
            ['value' => '1', 'label' => __('1')],
            ['value' => '2', 'label' => __('2')],
            ['value' => '3', 'label' => __('3')],
            ['value' => '1', 'label' => __('4')],
            ['value' => '1', 'label' => __('5')],
            ['value' => '1', 'label' => __('6')],
            ['value' => '1', 'label' => __('7')],
        ];
    }
}
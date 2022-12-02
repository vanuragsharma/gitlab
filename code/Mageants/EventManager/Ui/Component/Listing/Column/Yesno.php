<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Ui\Component\Listing\Column;

/**
 * Stores Class return array
 */ 
class Yesno extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return Array
     */
    public function getAllOptions()
    {
       return [
            ['value' => '0', 'label' => __('Disabled')],
            ['value' => '1', 'label' => __('Enabled')],
        ];
    }
}
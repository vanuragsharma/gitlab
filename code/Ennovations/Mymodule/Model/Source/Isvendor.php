<?php

namespace Ennovations\Mymodule\Model\Source;

class Isvendor extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions() {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('--Select--'), 'value' => ''],
                ['label' => __('Yes'), 'value' => 1],
                ['label' => __('No'), 'value' => 2]
            ];
        }
        return $this->_options;
    }
}

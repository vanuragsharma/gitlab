<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ShowPriceAfterLogin
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ShowPriceAfterLogin\Model\Config\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;

class ShowPriceAttributeOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
 
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        /* your Attribute options list*/
        $this->_options=[ ['label'=>'Yes', 'value'=>'1'],
                          ['label'=>'No', 'value'=>'0']
                         ];
        return $this->_options;
    }
 
    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
 
    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Custom Attribute Options  ' . $attributeCode . ' column',
            ],
        ];
    }
}

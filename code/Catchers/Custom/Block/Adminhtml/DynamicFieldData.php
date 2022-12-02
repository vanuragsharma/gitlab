<?php

namespace Catchers\Custom\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Catchers\Custom\Block\Adminhtml\Form\Field\FetchGroups;

class DynamicFieldData extends AbstractFieldArray
{
    private $dropdownRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn(
            'cusgroup',
            [
                'label' => __('Group Name'),
                'renderer' => $this->getDropdownRenderer(),
            ]
        );
        $this->addColumn(
            'cusgroupemail',
            [
                'label' => __('Group Email'),
                'size'  => '40px',
                'class' => 'required-entry',
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];
        $dropdownField = $row->getDropdownField();
        if ($dropdownField !== null)
        {
            $options['option_' . $this->getDropdownRenderer()->calcOptionHash($dropdownField)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    private function getDropdownRenderer()
    {
        if (!$this->dropdownRenderer) {
            $this->dropdownRenderer = $this->getLayout()->createBlock(
                FetchGroups::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]);
        }
        return $this->dropdownRenderer;
    }
}
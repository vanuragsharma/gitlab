<?php

namespace BoostMyShop\Supplier\Block\Transit\Filter;

class Supplier extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    protected $_suppliersFactory;
    protected $_transitCollection;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \Magento\Framework\DB\Helper $resourceHelper,
                                \BoostMyShop\Supplier\Model\ResourceModel\Supplier\CollectionFactory $suppliersFactory,
                                \BoostMyShop\Supplier\Model\ResourceModel\Transit\CollectionFactory $transitCollection,
                                array $data = [])
    {
        parent::__construct($context, $resourceHelper, $data);

        $this->_suppliersFactory = $suppliersFactory;
        $this->_transitCollection = $transitCollection;
    }

    /**
     * Get options
     *
     * @return array
     */
    protected function _getOptions()
    {
        $result = [];
        $result[] = ['value' => '', 'label' => ' '];
        foreach ($this->_suppliersFactory->create()->setOrder('sup_name', 'ASC') as $supplier) {
            $result[] = ['value' => $supplier->getId(), 'label' => $supplier->getsup_name()];
        }

        return $result;
    }

    /**
     * Get condition
     *
     * @return array|null
     */
    public function getCondition()
    {
        if ($this->getValue() === null) {
            return null;
        }

        $value = $this->getValue();

        if (isset($value['sup_id']) || isset($value['po_id']))
        {
            $collection = $this->_transitCollection->create()->init();

            if (isset($value['sup_id']))
            {
                $collection->addSupplierFilter($value['sup_id']);
            }

            if (isset($value['po_id']))
            {
                $collection->addPurchaseReferenceFilter($value['po_id']);
            }

            return ['in' => $collection->getAllProductIds()];
        }

        return null;
    }

    public function getHtml()
    {
        $value = $this->getValue();

        $supId = (isset($value['sup_id']) ? $value['sup_id'] : '');
        $html = '<select name="' . $this->_getHtmlName() . '[sup_id]" id="' . $this->_getHtmlId() . '"' . $this->getUiId(
                'filter',
                $this->_getHtmlName()
            ) . 'class="no-changes admin__control-select">';
        foreach ($this->_getOptions() as $option) {
            if (is_array($option['value'])) {
                $html .= '<optgroup label="' . $this->escapeHtml($option['label']) . '">';
                foreach ($option['value'] as $subOption) {
                    $html .= $this->_renderOption($subOption, $supId);
                }
                $html .= '</optgroup>';
            } else {
                $html .= $this->_renderOption($option, $supId);
            }
        }
        $html .= '</select>';

        $html .= '<input type="text" name="related_po[po_id]" placeholder="'.__('Purchase order #').'" id="transitGrid_filter_related_po" value="'.(isset($value['po_id']) ? $value['po_id'] : '').'" class="input-text admin__control-text no-changes" data-ui-id="supplier-transit-renderer-filter-po-0-filter-related-po">';

        return $html;
    }

}

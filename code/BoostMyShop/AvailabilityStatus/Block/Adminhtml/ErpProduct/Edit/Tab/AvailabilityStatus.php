<?php

namespace BoostMyShop\AvailabilityStatus\Block\Adminhtml\ErpProduct\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class AvailabilityStatus extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_storeCollection;
    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollection,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->_storeCollection = $storeCollection;
        $this->_coreRegistry = $coreRegistry;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('availabilityStatusGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setTitle(__('Availability statuses'));
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setPagerVisibility(false);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_storeCollection->create();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }


    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn('website', ['header' => __('Website'), 'index' => 'sku', 'filter' => false, 'sortable' => false, 'renderer' => 'BoostMyShop\AvailabilityStatus\Block\Adminhtml\ErpProduct\Renderer\Website']);
        $this->addColumn('name', ['header' => __('Store'), 'index' => 'name', 'filter' => false, 'sortable' => false]);
        $this->addColumn('message', ['header' => __('Message'), 'filter' => false, 'sortable' => false, 'renderer' => 'BoostMyShop\AvailabilityStatus\Block\Adminhtml\ErpProduct\Renderer\Message']);

        return $this;
    }

    public function getMainButtonsHtml()
    {
        //nothing
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getTabLabel()
    {
        return __('Availability statuses');
    }

    public function getTabTitle()
    {
        return __('Availability statuses');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        $excludedProductTypes = ['configurable', 'bundle', 'grouped', 'container'];

        if (in_array($this->getProduct()->getTypeId(), $excludedProductTypes))
            return true;
        else
            return false;
    }

}

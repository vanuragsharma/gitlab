<?php

namespace BoostMyShop\OrderPreparation\Block;

class Manifest extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = "Manifest/Manifest.phtml";

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Widget\Context $context,array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $this->buttonList->add('create_new_manifest', array(
            'label'   => __('Create new manifest'),
            'onclick' => "setLocation('{$this->getUrl('*/*/create')}')",
            'class'   => 'action-default scalable action-primary'
        ));

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Manifest\Grid', 'boostmyshop.Manifest.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

}
<?php

namespace BoostMyShop\OrderPreparation\Block;

class Batch extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = "Batch/Batch.phtml";

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
        $this->buttonList->remove('add_new');

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Batch\Grid', 'boostmyshop.Batch.grid')
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
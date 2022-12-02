<?php namespace BoostMyShop\AdvancedStock\Block\Warehouse;


class ImportSummary extends \Magento\Backend\Block\Template {

    /**
     * @var string
     */
    protected $_template = 'Warehouse/ImportSummary.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Header constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/Edit', ['w_id' => $this->getWarehouse()->getId()]);
    }

}
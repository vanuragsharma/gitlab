<?php

namespace BoostMyShop\Erp\Block\Products\Edit\Tab;

class Overview extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'Products/Edit/Tab/Overview.phtml';

    protected $_coreRegistry = null;
    protected $_productHelper = null;
    protected $_resultLayoutFactory = null;
    protected $_advancedStockConfig = null;
    protected $_orderPreparationConfig = null;

    protected $_figures = [];
    protected $_blocks = [];

    public function __construct(
        \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry,
        \BoostMyShop\OrderPreparation\Model\Product $productHelper,
        \BoostMyShop\AdvancedStock\Model\Config $advancedStockConfig,
        \BoostMyShop\OrderPreparation\Model\Config $orderPreparationConfig,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        array $data = [])
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
        $this->_productHelper = $productHelper;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_advancedStockConfig = $advancedStockConfig;
        $this->_orderPreparationConfig = $orderPreparationConfig;
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getBarcode()
    {
        if ($this->_advancedStockConfig->getBarcodeAttribute())
        {
            return $this->getProduct()->getData($this->_advancedStockConfig->getBarcodeAttribute());
        }
        else
            return __('No attribute configured');
    }

    public function getProductVolume()
    {
        if ($this->_orderPreparationConfig->getVolumeAttribute())
        {
            return $this->getProduct()->getData($this->_orderPreparationConfig->getVolumeAttribute());
        }
        else
            return __('No attribute configured');
    }

    public function getPackageNumber()
    {
        if ($this->_orderPreparationConfig->getPackageNumberAttribute())
        {
            return $this->getProduct()->getData($this->_orderPreparationConfig->getPackageNumberAttribute());
        }
        else
            return __('No attribute configured');
    }

    public function getAdditionalHtml()
    {
        $obj = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch('erp_product_overview_header_attribute', ['obj' => $obj, 'product' => $this->getProduct()]);
        return $obj->getHtml();
    }

    public function getImageUrl()
    {
        return $this->_productHelper->getImageUrl($this->getProduct()->getId());
    }

    public function getContent()
    {
        $layout = $this->_resultLayoutFactory->create();
        $layout->addHandle('erp_products_edit_overview');
        $layout->render();
    }

    public function getKeyFigures()
    {

        $this->_figures['Status'] = ($this->getProduct()->getStatus() == 1 ? 'Enabled' : 'Disabled');
        $this->_figures['Cost'] = number_format($this->getProduct()->getCost(), 2, '.', '');

        $this->_eventManager->dispatch('erp_product_edit_main_figures', ['product' => $this->getProduct(),  'block' => $this]);

        return $this->_figures;
    }

    public function addFigure($k, $v)
    {
        $this->_figures[$k] = $v;
    }

    public function addBlock($title, $content)
    {
        $this->_blocks[$title] = $content;
    }

    public function getContentBlocks()
    {
        $this->_eventManager->dispatch('erp_product_edit_main_blocks', ['product' => $this->getProduct(),  'block' => $this, 'layout' => $this->getLayout()]);

        return $this->_blocks;
    }

    public function getTabLabel()
    {
        return __('Overview');
    }

    public function getTabTitle()
    {
        return __('Overview');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

}

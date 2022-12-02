<?php

namespace BoostMyShop\Supplier\Block\Transit\Renderer;


class Sku extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_config;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\Supplier\Model\Config $config,
                                array $data = [])
    {
        parent::__construct($context, $data);
        $this->_config = $config;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        if ($this->_config->isErpIsInstalled())
            $url = $this->getUrl('erp/products/edit', ['id' => $row->getId()]);
        else
            $url = $this->getUrl('catalog/product/edit', ['id' => $row->getId()]);

        $html = '<a href="'.$url.'">'.$row->getSku().'</a>';
        return $html;
    }
    public function renderExport(\Magento\Framework\DataObject $row)
    {
        return $row->getSku();
    }

}
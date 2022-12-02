<?php
namespace BoostMyShop\OrderPreparation\Block\Manifest\View;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setId('manifest_tabs');
        $this->setDestElementId('grid_tab_content');
        $this->setTitle(__('Manifest information'));
    }

    protected function _prepareLayout()
    {
        $this->addTab(
            'general',
            [
                'label' => __('General'),
                'content' => $this->getLayout()->createBlock(
                    \BoostMyShop\OrderPreparation\Block\Manifest\View\Tab\General::class
                )->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'shipments',
            [
                'label' => __('Shipment'),
                'content' => $this->getLayout()->createBlock(
                    \BoostMyShop\OrderPreparation\Block\Manifest\View\Tab\Shipment::class
                )->toHtml(),
            ]
        );

        return parent::_prepareLayout();
    }
}
<?php
namespace BoostMyShop\OrderPreparation\Block\Batch\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setId('op_batch_tabs');
        $this->setDestElementId('batch_tab_content');
        $this->setTitle(__('Batch Information'));
    }

    protected function _prepareLayout()
    {
        $this->addTab(
            'detail',
            [
                'label' => __('Details'),
                'content' => $this->getLayout()->createBlock(
                    \BoostMyShop\OrderPreparation\Block\Batch\Edit\Tab\Main::class
                )->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'orders',
            [
                'label' => __('Orders'),
                'content' => $this->getLayout()->createBlock(
                    \BoostMyShop\OrderPreparation\Block\Batch\Edit\Tab\OrdersBlock::class
                )->toHtml(),
            ]
        );

        $this->_eventManager->dispatch('bms_op_batch_popup_tab', ['tabs' => $this, 'layout' => $this->getLayout()]);

        return parent::_prepareLayout();
    }
}
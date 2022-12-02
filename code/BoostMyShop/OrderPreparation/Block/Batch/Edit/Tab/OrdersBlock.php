<?php
namespace BoostMyShop\OrderPreparation\Block\Batch\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class OrdersBlock extends Base implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'BoostMyShop_OrderPreparation::Batch/tabs/orders.phtml';

    public function getGridHtml()
    {
        $block = $this->getLayout()
            ->createBlock(\BoostMyShop\OrderPreparation\Block\Batch\Edit\Tab\Orders::class)
            ->setBatch($this->getBatch())->toHtml();
        return $block;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Orders');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Orders');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
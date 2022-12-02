<?php

namespace BoostMyShop\Supplier\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class AdvancedStockLowStockGrid implements ObserverInterface
{

    public function execute(EventObserver $observer)
    {
        $grid = $observer->getEvent()->getgrid();

        $grid->addColumnAfter(
            'lead_time',
            [
                'header' => __('Lead time'),
                'index' => 'lead_time',
                'type' => 'number',
                'sortable' => false,
                'filter' => false,
                'align' => 'left'
            ],
            'average_per_week');

        $grid->addColumnAfter(
            'suppliers',
            [
                'header' => __('Suppliers'),
                'index' => 'entity_id',
                'sortable' => false,
                'align' => 'left',
                'renderer' => 'BoostMyShop\Supplier\Block\Replenishment\Renderer\Suppliers',
                'filter' => 'BoostMyShop\Supplier\Block\Replenishment\Filter\Suppliers'
            ],
            'qty_to_order');

        $grid->addColumnAfter(
            'discontinued',
            [
                'header' => __('Discontinued'),
                'index' => 'supply_discontinued',
                'type' => 'options',
                'options' => [0 => __('No'), 1 => __('Yes')]
            ],
            'suppliers');

        return $this;
    }
}


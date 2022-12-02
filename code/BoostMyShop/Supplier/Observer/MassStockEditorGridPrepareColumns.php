<?php

namespace BoostMyShop\Supplier\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class MassStockEditorGridPrepareColumns implements ObserverInterface
{

    public function execute(EventObserver $observer)
    {
        $grid = $observer->getEvent()->getgrid();

        $grid->addColumnAfter(
            'discontinued',
            [
                'header' => __('Discontinued'),
                'index' => 'supply_discontinued',
                'type' => 'options',
                'align' => 'center',
                'options' => ['' => ' ', 0 => __('No'), 1 => __('Yes')]
            ],
            'status');

        return $this;
    }
}


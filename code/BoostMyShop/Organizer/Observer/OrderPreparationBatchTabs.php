<?php

namespace BoostMyShop\Organizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class OrderPreparationBatchTabs implements ObserverInterface
{

    public function execute(EventObserver $observer)
    {
        $tabs = $observer->getEvent()->getTabs();
        $layout = $observer->getEvent()->getLayout();

        $tabs->addTab(
            'organizer',
            [
                'label' => __('Organizer'),
                'title' => __('Organizer'),
                'content' => $layout->createBlock('\BoostMyShop\Organizer\Block\Preparation\Batch\Tab\Organizer')->toHtml()
            ]
        );

        return $this;
    }


}

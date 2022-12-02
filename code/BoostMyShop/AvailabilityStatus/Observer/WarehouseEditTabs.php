<?php

namespace BoostMyShop\AvailabilityStatus\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class WarehouseEditTabs implements ObserverInterface
{
    protected $_config;

    /**
     * @param StockIndexInterface $stockIndex
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     */
    public function __construct(
        \BoostMyShop\AvailabilityStatus\Model\Config $config
    ) {
        $this->_config = $config;
    }

    public function execute(EventObserver $observer)
    {
        $tabs = $observer->getEvent()->gettabs();
        $layout = $observer->getEvent()->getlayout();

        if ($this->_config->getUseWarehouseDelay())
        {
            $tabs->addTab(
                'availability_section',
                [
                    'label' => __('Availability status'),
                    'title' => __('Availability status'),
                    'content' => $layout->createBlock('BoostMyShop\AvailabilityStatus\Block\Adminhtml\Warehouse\Edit\Tab\Availability')->toHtml()
                ]
            );
        }

    }

}
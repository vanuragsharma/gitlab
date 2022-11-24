<?php

namespace Yalla\Magentocatalog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

class CodAvailable implements ObserverInterface
{
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $result = $observer->getEvent()->getResult();
        $method_instance = $observer->getEvent()->getMethodInstance();
        $quote = $observer->getEvent()->getQuote();

        if ($quote === null || $method_instance->getCode() != 'cashondelivery')
            return;

        /* Disable All payment gateway exclude Your payment Gateway */
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$itemHelper = $objectManager->create('Mageplaza\PreOrder\Helper\Item');
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
        	$product = $item->getProduct();
			$isPreOrder = $itemHelper->isApplyForProduct($product);
            if ($isPreOrder) {
                $result->setData('is_available', false);
                break;
            }
        }

    }
}


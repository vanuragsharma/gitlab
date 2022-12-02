<?php

namespace BoostMyShop\OrderPreparation\Helper;


class CarrierTemplate
{
    protected $_templateCollectionFactory;

    public function __construct(
        \BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate\CollectionFactory $templateCollectionFactory
    )
    {
        $this->_templateCollectionFactory = $templateCollectionFactory;
    }

    public function getCarrierTemplateForOrder($orderInProgress, $warehouseId = null)
    {
        $storeId = $orderInProgress->getOrder()->getStoreId();
        $shippingMethod = $orderInProgress->getOrder()->getShippingMethod();
        return $this->getCTFromMethod($shippingMethod, $warehouseId,$storeId);
    }

    public function getCTFromMethod($shippingMethod, $warehouseId = null,$storeId = null)
    {
        if ($shippingMethod)
        {
            $collection = $this->_templateCollectionFactory->create()
                            ->addActiveFilter()
                            ->addShippingMethodFilter($shippingMethod);

            if ($storeId)
                $collection->addStoreFilter($storeId);
            if ($warehouseId)
                $collection->addWarehouseFilter($warehouseId);
            $template = $collection->getFirstItem();
            if ($template->getId())
                return $template;
        }

        return false;
    }

    public function getRates($inProgress, $carrierTemplateIds)
    {
        $rates = [];

        $templates = $this->_templateCollectionFactory->create()->addFieldToFilter('ct_id', ['in' => $carrierTemplateIds]);
        foreach($templates as $template)
        {
            if ($template->getRenderer())
            {
                $result = ['success' => true, 'rates' => [], 'message' => ''];
                try
                {
                    $result['rates'] = $template->getRenderer()->getRates($template, $inProgress);

                    //sort by price
                    usort($result['rates'], function ($a, $b) {
                        if ($a['price'] < $b['price'])
                            return -1;
                        else
                            return 1;
                    });
                }
                catch(\Exception $ex)
                {
                    $result['success'] = false;
                    $result['message'] = $ex->getMessage();
                }

                $rates[$template->getct_name()] = $result;
            }

        }

        return $rates;
    }

    public function hasTemplateWithRates()
    {
        $templates = $this->_templateCollectionFactory->create();
        foreach($templates as $template) {

            if ($template->getRenderer() && $template->getRenderer()->canGetRates())
                return true;
        }

        return false;
    }

    public function getTrackingUrl($object, $trackingNumber)
    {
        $shippingMethod = null;
        $objectType = get_class($object);
        $objectType = str_replace('\\Interceptor', '', $objectType);

        switch($objectType)
        {
            case 'Magento\Sales\Model\Order':
                $shippingMethod = $object->getShippingMethod();
                break;
            case 'Magento\Sales\Model\Order\Shipment':
                $shippingMethod = $object->getOrder()->getShippingMethod();
                break;
        }

        if(!$shippingMethod)
            return false;

        $carrierTemplate = $this->getCTFromMethod($shippingMethod);
        if($carrierTemplate && $carrierTemplate->getRenderer())
        {
            return $carrierTemplate->getRenderer()->getTrackingUrl($trackingNumber);
        }

        return false;
    }

    public function getTrackingLink($obj, $trackingNumber)
    {
        $trackingUrl = $this->getTrackingUrl($obj, $trackingNumber);
        if($trackingUrl)
            return '<a href="'.$trackingUrl.'" target="_new">'.$trackingNumber.'</a>';

        return false;
    }
}
<?php
namespace BoostMyShop\OrderPreparation\Model\Batch\Type;

class UniqueProduct extends \BoostMyShop\OrderPreparation\Model\Batch\AbstractType
{
    const CODE = 'unique';

    public function getCode()
    {
        return self::CODE;
    }

    public function getName()
    {
        return "Unique";
    }

    public function getCandidateOrders($warehouseId, $carrier = null)
    {
        $orders = [];
        $collection = $this->getOrders($warehouseId);

        foreach ($collection as $order) {
            if($carrier) {
                if(!$this->orderMatchesToCarrier($order, $carrier))
                {
                    continue;
                }
            }

            if((int)$order->getTotalItemCount() == 1 && (int)$order->getqty_ordered() == 1)
            {
                $orders[$order->getproduct_id()][] = $order->getId();
            }
        }

        $minimumOrderCount = $this->getUniqueProductMinimumOrderCount();
        $uniqueOrders = [];
        foreach ($orders as $productId => $orderArray)
        {
            if(count($orderArray) >= $minimumOrderCount)
            {
                $uniqueOrders = array_merge($uniqueOrders, $orderArray);
            }
        }

        return $uniqueOrders;
    }

    public function getPdfClass()
    {
        return \BoostMyShop\OrderPreparation\Model\Batch\Type\UniqueProduct\Pdf::class;
    }

    public function getAdditionalActions($batch)
    {
        if($batch->getbob_status() != \BoostMyShop\OrderPreparation\Model\Batch::STATUS_COMPLETE) {
            $actions[] = ['label' => __('Confirm shipping'), 'onclick' => 'confirmShipment(\''.$this->_urlBuilder->getUrl('orderpreparation/batch/ConfirmShipment', ['bob_id' => $batch->getId()]).'\')',  'url' => "#", 'target' => ''];
            return $actions;
        }
        return [];
    }

    public function getCandidateOrdersByProductId($warehouseId, $productId, $carrier = null)
    {
        $orders = [];
        $collection = $this->getOrders($warehouseId);

        foreach ($collection as $order) {
            if($carrier) {
                if(!$this->orderMatchesToCarrier($order, $carrier))
                    continue;
            }

            if((int)$order->getTotalItemCount() == 1 && (int)$order->getqty_ordered() == 1)
                $orders[$order->getproduct_id()][] = $order->getId();
        }

        $minimumOrderCount = $this->getUniqueProductMinimumOrderCount();
        $uniqueOrders = [];
        foreach ($orders as $p_id => $orderArray)
        {
            if($p_id == $productId && count($orderArray) >= $minimumOrderCount) {
                $uniqueOrders = $orderArray;
                break;
            }
        }

        return $uniqueOrders;

    }
}
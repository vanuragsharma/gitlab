<?php
namespace BoostMyShop\OrderPreparation\Model\Batch\Type;

class MultipleProduct extends \BoostMyShop\OrderPreparation\Model\Batch\AbstractType
{
    const CODE = 'multiple';

    public function getCode()
    {
        return self::CODE;
    }

    public function getName()
    {
        return "Multiple";
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

            if((int)$order->getTotalItemCount() > 1 || ($order->getTotalItemCount() == 1 && $order->getqty_ordered() > 1))
            {
                $orders[] = $order->getId();
            }
        }

        return $orders;
    }

    public function getPdfClass()
    {
        return \BoostMyShop\OrderPreparation\Model\Batch\Type\MultipleProduct\Pdf::class;
    }
}
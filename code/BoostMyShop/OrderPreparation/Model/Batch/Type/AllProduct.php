<?php
namespace BoostMyShop\OrderPreparation\Model\Batch\Type;

class AllProduct extends \BoostMyShop\OrderPreparation\Model\Batch\AbstractType
{
    const CODE = 'all';

    public function getCode()
    {
        return self::CODE;
    }

    public function getName()
    {
        return "All";
    }

    public function getCandidateOrders($warehouseId, $carrier = null)
    {
        $orders = [];
        $collection = $this->getOrders($warehouseId);

        foreach ($collection as $order) {
            if($carrier) {
                if(!$this->orderMatchesToCarrier($order, $carrier))
                    continue;
            }

            $orders[] = $order->getId();
        }

        return $orders;
    }

    public function getPdfClass()
    {
        return \BoostMyShop\OrderPreparation\Model\Batch\Type\AllProduct\Pdf::class;
    }
}
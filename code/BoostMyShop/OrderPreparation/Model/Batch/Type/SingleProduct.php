<?php
namespace BoostMyShop\OrderPreparation\Model\Batch\Type;

class SingleProduct extends \BoostMyShop\OrderPreparation\Model\Batch\AbstractType
{
    const CODE = 'single';

    public function getCode()
    {
        return self::CODE;
    }

    public function getName()
    {
        return "Single";
    }

    public function getCandidateOrders($warehouseId, $carrier = null)
    {
        $singleProductOrders = [];
        $collection = clone $this->getOrders($warehouseId);

        foreach ($collection as $order) {
            if($carrier) {
                if(!$this->orderMatchesToCarrier($order, $carrier))
                {
                    continue;
                }
            }

            if((int)$order->getTotalItemCount() == 1)
            {
                if ((int)$order->getqty_ordered() == 1)
                    $singleProductOrders[] = $order->getId();
            }
        }

        return $singleProductOrders;
    }

    public function getPdfClass()
    {
        return \BoostMyShop\OrderPreparation\Model\Batch\Type\SingleProduct\Pdf::class;
    }
}

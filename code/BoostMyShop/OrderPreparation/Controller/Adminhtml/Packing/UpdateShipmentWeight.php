<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class UpdateShipmentWeight extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $id = $this->getRequest()->getParam('order_id');
        $orderInProgress = $this->currentOrderInProgress();
        try
        {
            $totalWeight = $this->getRequest()->getPost('total_weight');
            $parcelCount = $this->getRequest()->getPost('parcel_count');

            $parcelHeight = $this->getRequest()->getPost('parcel_height');
            $parcelWidth = $this->getRequest()->getPost('parcel_width');
            $parcelLength = $this->getRequest()->getPost('parcel_length');
            $boxDetails = $this->getRequest()->getPost('boxes');
            $ipTotalWeight = $totalWeight;
            if($boxDetails) {
                foreach ($boxDetails as $box)
                    $ipTotalWeight += $box['total_weight'];
            }
            $boxDetails[1] = [
                'total_weight' => $totalWeight,
                'parcel_count' => $parcelCount,
                'parcel_length' => $parcelLength,
                'parcel_width' => $parcelWidth,
                'parcel_height' => $parcelHeight,
            ];
            $boxDetailsJson = json_encode($boxDetails);
            $orderInProgress->addParcelBoxes($boxDetailsJson);

            $orderInProgress->setip_total_weight($ipTotalWeight)
                            ->setip_parcel_count($parcelCount)
                            ->setip_height($parcelHeight)
                            ->setip_width($parcelWidth)
                            ->setip_length($parcelLength)
                            ->save();

            if ($orderInProgress->getip_shipment_id())
            {
                $shipment = $this->_shipmentRepository->get($orderInProgress->getip_shipment_id());
                $shipment->setTotalWeight($ipTotalWeight)->save();
            }

            $this->messageManager->addSuccess(__('Boxes updated successfully.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
        }

        $this->_redirect('*/*/Index', ['order_id' => $id]);

    }

    public function currentOrderInProgress()
    {
        return $this->_coreRegistry->registry('current_packing_order');
    }
}

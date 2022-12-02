<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class ConfirmPacking extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();

        try
        {
            $quantities = $this->getRequest()->getPost('products');
            $totalWeight = $this->getRequest()->getPost('total_weight');
            $parcelCount = $this->getRequest()->getPost('parcel_count');

            $parcelHeight = $this->getRequest()->getPost('parcel_height');
            $parcelWidth = $this->getRequest()->getPost('parcel_width');
            $parcelLength = $this->getRequest()->getPost('parcel_length');

            $createInvoice = $this->_configFactory->create()->getCreateInvoice();
            $createShipment = $this->_configFactory->create()->getCreateShipment();

            $this->_eventManager->dispatch('bms_orderpreparation_order_before_pack', ['order_in_progress' => $this->currentOrderInProgress(), 'products' => $quantities, 'request' => $this->getRequest()]);

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
            $this->currentOrderInProgress()->addParcelBoxes($boxDetailsJson);

            $this->currentOrderInProgress()->pack($createShipment, $createInvoice, $quantities, $ipTotalWeight, $parcelCount, $parcelHeight, $parcelWidth, $parcelLength);

            //specific for shiptheory
            if($parcelCount > 1) {
                $message = 'Boxes = ' . $parcelCount;
                $orderId = $this->currentOrderInProgress()->getip_order_id();
                $order = $this->_orderFactory->create()->load($orderId);
                $order->addStatusHistoryComment($message)->save();
            }

            $this->_eventManager->dispatch('bms_orderpreparation_order_after_pack', ['order_in_progress' => $this->currentOrderInProgress(), 'products' => $quantities, 'request' => $this->getRequest()]);

            $this->messageManager->addSuccess(__('Order packed.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
            $this->_logger->logException($ex);
        }

        $this->_redirect('*/*/Index', ['order_id' => $this->currentOrderInProgress()->getId(), 'download' => 1      ]);
    }

    public function currentOrderInProgress()
    {
        return $this->_coreRegistry->registry('current_packing_order');
    }

}

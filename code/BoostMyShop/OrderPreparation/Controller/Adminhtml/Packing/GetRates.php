<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

class GetRates extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{

    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('ip_id');
        $inProgress = $this->_inProgressFactory->create()->load($id);
        $this->_coreRegistry->register('current_inprogress', $inProgress);

        $inProgress->setip_total_weight($this->getRequest()->getParam('total_weight'));
        $inProgress->setip_parcel_count($this->getRequest()->getParam('parcel_count'));
        $inProgress->setip_length($this->getRequest()->getParam('parcel_length'));
        $inProgress->setip_width($this->getRequest()->getParam('parcel_width'));
        $inProgress->setip_height($this->getRequest()->getParam('parcel_height'));
        $inProgress->save();

        if ($this->getRequest()->getParam('templates'))
        {
            $templateIds = array_keys($this->getRequest()->getParam('templates'));
            $rates = $this->_carrierTemplateHelper->getRates($inProgress, $templateIds);
        }
        else
            $rates = [];

        $this->_initAction();

        $resultLayout = $this->_resultLayoutFactory->create();

        $block = $resultLayout->getLayout()->getBlock('rates');
        $block->setRates($rates);

        $this->_view->renderLayout();

    }
}

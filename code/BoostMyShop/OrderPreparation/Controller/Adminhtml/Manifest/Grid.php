<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest;

class Grid extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->renderLayout();
    }
}

<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest;

class Index extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manifest'));
        $this->_view->renderLayout();
    }
}

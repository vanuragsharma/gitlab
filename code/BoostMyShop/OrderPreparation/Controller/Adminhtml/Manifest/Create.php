<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest;

class Create extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Manifest
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Create new manifest'));
        $this->_view->renderLayout();
    }
}

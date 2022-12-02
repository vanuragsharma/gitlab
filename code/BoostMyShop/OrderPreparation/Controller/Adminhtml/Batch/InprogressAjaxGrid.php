<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Batch;

use Magento\Framework\Controller\ResultFactory;

class InprogressAjaxGrid extends \Magento\Backend\App\AbstractAction
{
    protected $_registry;
    protected $_batchFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\OrderPreparation\Model\BatchFactory $batchFactory
    ) {
        $this->_registry = $registry;
        $this->_batchFactory = $batchFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $bobId = $this->getRequest()->getParam("bob_id");
        $batch = $this->_batchFactory->create()->load($bobId);
        $this->_registry->register('current_batch', $batch);

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}

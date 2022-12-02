<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/  
namespace Mageants\EventManager\Controller\Adminhtml\Data;

use Magento\Framework\Controller\ResultFactory;

class AddRow extends \Magento\Backend\App\Action
{
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Mageants\EventManager\Model\EventdataFactory $Eventdata,
        \Magento\Backend\Model\Session\Proxy $session
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->Eventdata = $Eventdata;
        $this->session = $session;
        
    }

    /**
     * Mapped Grid List page.
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $rowId = (int) $this->getRequest()->getParam('e_id');       
        $rowData = $this->Eventdata->create();
        
        if ($rowId) {
           
           $rowData = $rowData->load($rowId);
           
           $rowTitle = $rowData->getTitle();
           
           if (!$rowData->getEId()) {
               $this->messageManager->addError(__('row data no longer exist.'));
               return;
           }
        }

        $data = $rowData->getData();
        
        if (!empty($data)) {

          $rowData->setData($data);
       }

       $this->coreRegistry->register('row_data', $rowData);
       $this->coreRegistry->register('my_item', $rowData);
       $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
       $title = $rowId ? __('Edit Event ').$rowTitle : __('Add Events');
       $resultPage->getConfig()->getTitle()->prepend($title);
       return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mageants_EventManager::add_row');
    }
}
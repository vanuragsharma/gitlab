<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/

namespace Mageants\EventManager\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Mageants\EventManager\Model\EventdataFactory $eventdata,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Mageants\EventManager\Helper\Data $eventmanagerhelper
        
        
    ) {
        parent::__construct($context);
        $this->eventmanagerhelper =  $eventmanagerhelper;
        $this->_eventdata = $eventdata;
        $this->_messageManager = $messageManager;
        
        
     } 
     /**
     * Listing event in frontend
     */ 
    public function execute()
    {
    	if ($this->eventmanagerhelper->getConfigValue('event/general/EnableModule') == 0) {
            return $this->_forward('index', 'noroute', 'cms');
        }


        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
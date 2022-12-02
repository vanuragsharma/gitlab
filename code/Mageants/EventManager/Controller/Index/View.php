<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Controller\Index;

class View extends \Magento\Framework\App\Action\Action
{
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Mageants\EventManager\Helper\Data $eventmanagerhelper,
        \Mageants\EventManager\Model\EventdataFactory $Eventdata
        
    ) {
        parent::__construct($context);
        $this->_Eventdata = $Eventdata;
        $this->eventmanagerhelper =  $eventmanagerhelper;
        
        
    }
    /**
     * Perform View  Action
     */ 
    public function execute()
    {
    	if ($this->eventmanagerhelper->getConfigValue('event/general/EnableModule') == 0) {
            return $this->_forward('index', 'noroute', 'cms');
        }
        else {

            $this->_view->loadLayout();
            $this->_view->getLayout()->initMessages();
            $this->_view->renderLayout();

        }
		


        
    }
}
<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Controller\Index;

class CalenderView extends \Magento\Framework\App\Action\Action
{
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Mageants\EventManager\Model\EventdataFactory $Eventdata
        
    ) {
        parent::__construct($context);
        $this->_Eventdata = $Eventdata;
        //$this->session = $session;
        
    } 
    /**
     * Display EventCalender view
     */ 
     public function execute()
    {   
		
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
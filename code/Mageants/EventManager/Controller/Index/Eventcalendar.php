<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Controller\Index;

class Eventcalendar extends \Magento\Framework\App\Action\Action
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
     * Display Event in Calender view
     */   
    public function execute()
     {  
        $data = array();
        
        $Eventdata = $this->_Eventdata->create();
        $eventcollection = $Eventdata->getCollection()->addFieldToFilter("status" , 1);

        foreach ($eventcollection as $event) {

             $data[] = array(
                'id'   => $event->getEId(),
                'color' => $event->getColor(),
                'title'   => $event->getEventTitle(),
                'start'   => $event->getStartDate(),
                'end'   => $event->getEndDate()

            );
            
    }

            
            echo json_encode($data);

    	
		


            
     }
}

?>
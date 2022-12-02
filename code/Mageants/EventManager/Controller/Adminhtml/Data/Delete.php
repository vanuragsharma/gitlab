<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Controller\Adminhtml\Data;
 
use Magento\Backend\App\Action\Context;
/**
 * Perform delete action for ProductLabel
 */
class Delete extends \Magento\Backend\App\Action
{
	
    public function __construct(
    	Context $context, 
    	\Mageants\EventManager\Model\EventdataFactory $Eventdata 
    	
    )
    {
        $this->Eventdata=$Eventdata;
       
        parent::__construct($context);
    }

    /**
     * Perform Delete Action
     */
    public function execute()
	{  
		
			$id = $this->getRequest()->getParam('e_id');
            try{

               $eventdata = $this->Eventdata->create();
               $eventdata->load($id);
               $eventdata->delete();


            
            } catch (Exception $e) {
                $this->messageManager->addError(__($ex->getMessage()));     
            }

            $this->messageManager->addSuccess(__('Event successfully deleted'));
        
        $this->_redirect('event/data/index');
    }

	

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mageants_EventManager::delete');
    }
}


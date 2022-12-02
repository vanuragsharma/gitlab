<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Controller\Adminhtml\Data;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\filesystem;
 
 
class Save extends \Magento\Backend\App\Action
{
    var $Eventdata;
 
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Mageants\EventManager\Model\EventdataFactory $Eventdata,
         UploaderFactory $uploaderFactory,
         AdapterFactory $adapterFactory,
         Filesystem $filesystem) 
    {
        parent::__construct($context);
        $this->Eventdata = $Eventdata;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * save data into database
     */

     public function execute()
    {
        
        $data = $this->getRequest()->getPostValue();
        //var_dump($data);exit();
        if (!array_key_exists("image",$data))
            {
            $data['image'] = '';
            }
        if (!array_key_exists("thumbnail_image",$data))
            {
            $data['thumbnail_image'] = '';
            }
        /*echo "<pre>";
        var_dump($data);exit();*/
        $startdate = $data['start_date'];
        $enddate = $data['end_date'];
        if(!empty($data['productassign'])){

        $productassign = $data['productassign'];

        $convertidtoarray = explode(',', $productassign); 
        //var_dump($convertidtoarray);exit();
            $ids = [];
            $Id;
            foreach ($convertidtoarray as $pid) {

                $removespecialchar = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s' , '' ,$pid);   
                $ids[] = $removespecialchar;

            }
            $Id = implode(",", $ids);
            
            $data['productassign'] = $Id; 
            
            } 
            /*else {

                 $Id = $data['productassign'];


            }*/
    

        if(!empty($data['image'])){
             $img = '';
              if(is_array($data['image'])){
                $images = [];
                foreach ($data['image'] as $key => $image) {
                    $images[] = $image['name'];
                }
                $img = implode(",", $images);
                $data['image'] = $img; 
                
             }
             else{
              $img = $data['image'];
             }
        }

        

        if(!empty($data['thumbnail_image'])){
             $img = '';
              if(is_array($data['thumbnail_image'])){
                $images = [];
                foreach ($data['thumbnail_image'] as $key => $image) {
                    $images[] = $image['name'];
                }
                $img = implode(",", $images);
                $data['thumbnail_image'] = $img; 
                
             }
             else{
              $img = $data['thumbnail_image'];
             }
        }


      if (!$data) {
            $this->_redirect('event/data/addrow');
            return;
        }
            
        try {
              $rowData = $this->Eventdata->create();
              $rowData->setData($data); 
              if (isset($data['id'])) {
                 $rowData->setEId($data['id']);
              }
              if($startdate > $enddate){

              $this->messageManager->addError(__('Make sure the End Date of event  is grater than or the same as the Start Date of event.'));
              $resultRedirect = $this->resultRedirectFactory->create();
              return $resultRedirect->setPath('*/*/addrow' , ['e_id' => $rowData->getEId(), '_current' => true]);

            }
            if(!empty($data['youtubeurl'])){
            if (!preg_match('@^(?:https://(?:www\\.)?youtube.com/)(watch\\?v=|v/)([a-zA-Z0-9_]*)@', $data['youtubeurl'])) {
               $this->messageManager->addError(__('Please Enter Valid Youtube Video Url'));
              $resultRedirect = $this->resultRedirectFactory->create();
              return $resultRedirect->setPath('*/*/addrow' , ['e_id' => $rowData->getEId(), '_current' => true]);


              }
            }


              $rowData->save();

              
           } catch (\Exception $e) {
             $this->messageManager->addError(__($e->getMessage()));
           }
           $this->messageManager->addSuccess(__('Event has been successfully saved.'));
           if($this->getRequest()->getParam('back')){
            
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/addrow', ['e_id' => $rowData->getEId(), '_current' => true]);
            }
            $this->_redirect('event/data/index');
    }
 
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mageants_EventManager::save');
    }
}
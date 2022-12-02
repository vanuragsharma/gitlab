<?php
/**
 * @category Mageants Productimage
 * @package Mageants_Productimage
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\EventManager\Model\Eventdata;

use Magento\Store\Model\StoreManagerInterface;
use Mageants\EventManager\Model\ResourceModel\Eventdata\CollectionFactory;
//use Mageants\Productimage\Model;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $_loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $CollectionFactory,
        StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $CollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->_storeManager=$storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $baseurl =  $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }

        $items = $this->collection->getItems();

        foreach ($items as $contact) {
            $temp = $this->_loadedData[$contact->getId()] = $contact->getData();
            /*echo "<pre>";
            var_dump($temp);exit();*/
            
            if ($temp['image']) {
                
                $multi_img = [];
                $multi_img = explode(",", $temp['image']);
                $img = [];
                foreach ($multi_img as $key => $item) {
                
                $img[$key]['name'] = $item;
                $img[$key]['url'] = $baseurl.'eventmanager/tmp/image/'.$item;
                $temp['image'] = $img;
               
             }

             if($temp['thumbnail_image']){
                $thumbnailimage = explode(",", $temp['thumbnail_image']);
                $thumbimg = [];
                foreach ($thumbnailimage as $key => $thimage) {
                
                $thumbimg[$key]['name'] = $thimage;
                $thumbimg[$key]['url'] = $baseurl.'eventmanager/tmp/image/'.$thimage;
                $temp['thumbnail_image'] = $thumbimg;
               
             }
             }
            
            
            $data = $this->dataPersistor->get('event_data');

            
            if (!empty($data)) {
                //echo "hjhjhc";exit;
                $page = $this->collection->getNewEmptyItem();
               
                $this->loadedData[$page->getId()] = $page->getData();   

                $this->dataPersistor->clear('event_data');
            }else {
                
                if ($contact->getData('image') != null || $contact->getData('thumbnail_image') != null  ) {
                    $parseData[$contact->getId()] = $temp;
                   
                     return $parseData;  
                } else {
                    return $this->_loadedData;
                }
            }
                   
                }
                
            
        }
        return $this->_loadedData;
    }
    public function getMediaUrl()
    {
        $mediaUrl = $this->_storeManager->getStore()
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl;
    }
}

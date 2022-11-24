<?php

namespace Yalla\Vendors\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper {

    const XML_PATH_LEZAMBANNER = 'vendor_logo/';

    public function getBannerUrl($banner_name = '') {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $media_url = $objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        
	if(!empty($banner_name)){
            if($this->CheckBannerExist($banner_name)){
                return $media_url . 'vendor_logo/' . $banner_name;
            }else{
                return false;
            }
        }

        return $media_url.'vendor_logo/'.$banner_name;
    }

    public function CheckBannerExist($banner_file) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        
        $file_path = $directory->getPath('media').'/vender_logo/'.$banner_file;
        
        if(file_exists($file_path)){
            return true;
        }
        return false;
    }

}

<?php
/**
 * @category   Mageants EventManager
 * @package    Mageants_EventManager
 * @copyright  Copyright (c) 2019 Mageants
 * @author     Mageants Team <support@Mageants.com>
 */
 namespace Mageants\EventManager\Block\Index;
 
use Magento\Framework\View\Element\Template\Context;
use Mageants\EventManager\Model\EventdataFactory;
use Mageants\EventManager\Helper\Data;


class View extends \Magento\Framework\View\Element\Template
{

  public function __construct(
        Context $context,
        EventdataFactory $Eventdata,
        \Mageants\EventManager\Model\ResourceModel\Eventdata\CollectionFactory $Eventcollection,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        Data $helperData,
        \Magento\Catalog\Helper\ImageFactory $helperImageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepos,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->_Eventdata = $Eventdata;
        $this->_eventCollectionFactory = $Eventcollection;
        $this->_productloader = $_productloader;
        $this->helperData = $helperData;
        $this->assetRepos = $assetRepos;
        $this->helperImageFactory = $helperImageFactory;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    
    public function getEventviewCollection()
    {
        $Eventdata = $this->_Eventdata->create();
        $eventcollection = $Eventdata->getCollection();
        return $eventcollection;
    }

    public function getEventId()
    {
        $urlprefix = $this->getRequest()->getParam('id');
        $Urlprefix = str_replace(".html","","$urlprefix");
        $Eventdata = $this->_eventCollectionFactory->create();
        $Eventdata->addFieldToFilter('urlprefix', ['eq' => $Urlprefix]);
        
        if (!empty($Eventdata->getData())) {
            foreach ($Eventdata->getData() as $event) {
                
                $result = $event['e_id'];
                
            }
            return $result;
        }
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->_storeManager->getStore()
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl;
    }

    

    public function getBaseUrl()
    {

      $baseurl = $this->_storeManager->getStore()->getBaseUrl();
      return $baseurl;
    }
    public function getProductCollection($id){

        
       return $this->_productloader->create()->load($id);  


    }
    public function getMapZoomDefault()
    {
        return ((int) $this->helperData->getConfigValue('event/general/map')) ?: 20;
        
    }

    public function getPlaceHolderImage()
    {
        $imagePlaceholder = $this->helperImageFactory->create();
        return $this->assetRepos->getUrl($imagePlaceholder->getPlaceholder('image'));
    }

     public function getFilteredContent($content = '')
    {
        return $this->filterProvider->getPageFilter()->filter($content);
    }

}
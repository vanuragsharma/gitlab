<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
 namespace Mageants\EventManager\Block\Index;
 
use Magento\Framework\View\Element\Template\Context;
use Mageants\EventManager\Model\EventdataFactory;
use Mageants\EventManager\Block\Product\ProductList\Toolbar;



/**
 *  List block
 */
class Index extends \Magento\Framework\View\Element\Template
{


     protected $_defaultToolbarBlock = Toolbar::class;

     const STORE_ID = "storeview";

    
    public function __construct(
        Context $context,
        \Mageants\EventManager\Helper\Data $eventdatahelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Magento\Catalog\Helper\ImageFactory $helperImageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepos,
        EventdataFactory $Eventdata
    ) {
        $this->_Eventdata = $Eventdata;
        $this->_eventdatahelper = $eventdatahelper;
        $this->_storeManager = $storeManager;
        $this->assetRepos = $assetRepos;
        $this->helperImageFactory = $helperImageFactory;
        parent::__construct($context);
    }
    /**
     * @return Mageants\EventManager\Model\eventdata\collection
     */  
    
    public function getEventCollection()
    {
        
        $defaultToolbar = $this->getToolbarBlock();
        $currentorder = $defaultToolbar->getCurrentOrder();
        $dir = $defaultToolbar->getCurrentDirection();
        $Eventdata = $this->_Eventdata->create();
        

        if($currentorder=='start_date' ) {

            
            if($dir == 'asc'){
                $collection = $Eventdata->getCollection()->addFieldToFilter("status" , 1)
                ->addFieldToFilter(self::STORE_ID, ['in' => [$this->_storeManager->getStore()->getId(),0]])   
                ->setOrder('start_date', 'ASC');
            }else {
                $collection = $Eventdata->getCollection()->addFieldToFilter("status" , 1)
                ->addFieldToFilter(self::STORE_ID, ['in' => [$this->_storeManager->getStore()->getId(),0]])
                ->setOrder('start_date', 'DESC');
                
            }


        }elseif ($currentorder=='end_date') {

           if($dir == 'asc'){
                $collection = $Eventdata->getCollection()->addFieldToFilter("status" , 1)
                ->addFieldToFilter(self::STORE_ID, ['in' => [$this->_storeManager->getStore()->getId(),0]])
                ->setOrder('end_date', 'ASC');
            }else {
                $collection = $Eventdata->getCollection()->addFieldToFilter("status" , 1)
                ->addFieldToFilter(self::STORE_ID, ['in' => [$this->_storeManager->getStore()->getId(),0]])
                ->setOrder('end_date', 'DESC');
            }
        }else{

            if($dir == 'asc'){
                $collection = $Eventdata->getCollection()->addFieldToFilter("status" , 1)
                ->addFieldToFilter(self::STORE_ID, ['in' => [$this->_storeManager->getStore()->getId(),0]])
                ->setOrder('event_title', 'ASC');
            }else {
                $collection = $Eventdata->getCollection()->addFieldToFilter("status" , 1)
                ->addFieldToFilter(self::STORE_ID, ['in' => [$this->_storeManager->getStore()->getId(),0]])
                ->setOrder('event_title', 'DESC');

            }

        }

        
        
        return $collection;             
            
    }
    /**
     * @return media URL
     */  

    public function getMediaUrl()
    {
        $mediaUrl = $this->_storeManager->getStore()
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl;
    }
    /**
     * @return CurrentMode 
     */

    public function getMode()
    {
        if ($this->getChildBlock('toolbar')) {
            return $this->getChildBlock('toolbar')->getCurrentMode();
        }

        return $this->getDefaultListingMode();
    }

    /**
     * @return DefaultMode 
     */

    private function getDefaultListingMode()
    {
        // default Toolbar when the toolbar layout is not used
        $defaultToolbar = $this->getToolbarBlock();
        $availableModes = $defaultToolbar->getModes();

        // layout config mode
        $mode = $this->getData('mode');

        if (!$mode || !isset($availableModes[$mode])) {
            // default config mode
            $mode = $defaultToolbar->getCurrentMode();
        }

        return $mode;
    }

    /**
     * @return  ToolbarBlock
     */
    public function getToolbarBlock()
    {
        $block = $this->getToolbarFromLayout();
        
        if (!$block) {
            $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        }

        return $block;
    }

    /**
     * @return ToolbarLayout 
     */
    private function getToolbarFromLayout()
    {
        $blockName = $this->getToolbarBlockName();



        $toolbarLayout = false;

        if ($blockName) {
            $toolbarLayout = $this->getLayout()->getBlock($blockName);
        }

        return $toolbarLayout;
    }

    /**
     * prepareLayout 
     */
    protected function _prepareLayout()
    {
         parent::_prepareLayout();
         
         $this->pageConfig->getTitle()->set(__($this->_eventdatahelper->getConfigValue('event/seoconfigration/pagetitle')));


        if ($this->getEventCollection()) {
        $pager = $this->getLayout()->createBlock(
            'Magento\Theme\Block\Html\Pager',
            'test.news.pager'
        )->setAvailableLimit(array(5=>5,10=>10,15=>15))->setShowPerPage(true)->setCollection(
            $this->getEventCollection()
        );
        $this->setChild('pager', $pager);
        $this->getEventCollection()->load();
    }
    return $this;
    }

    /**
     * @return pager 
     */
    public function getPagerHtml()
    {
    
       return $this->getChildHtml('pager');
    
    }

    public function getStoreId()
    {
        $test = $this->_storeManager->getStore()->getId();
        echo die($test);
    }

    public function getPlaceHolderImage()
    {
        $imagePlaceholder = $this->helperImageFactory->create();
        return $this->assetRepos->getUrl($imagePlaceholder->getPlaceholder('small_image'));
    }

}
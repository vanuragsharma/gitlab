<?php
 namespace Yalla\Theme\Block\Home;

 class Home extends \Magento\Framework\View\Element\Template
 {
    protected $helper;
    protected $scopeConfig;
   public function __construct(\Magento\Framework\View\Element\Template\Context $context,\Yalla\Clients\Helper\Data $helperData,\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig){
    $this->helper = $helperData;
    $this->scopeConfig = $scopeConfig;
    parent::__construct($context);
    }

   public function _prepareLayout()
   {
    return parent::_prepareLayout();
   }

   public function getConfig($config_path)
   {
    return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
   }
  
 }

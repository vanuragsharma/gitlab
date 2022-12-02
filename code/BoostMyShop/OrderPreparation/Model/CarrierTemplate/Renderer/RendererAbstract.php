<?php

namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManagerFactory;

abstract class RendererAbstract
{
    protected $objectManagerFactory;
    protected $objectManager;
    protected $_config;
    protected $_directory;
    protected $_filesystem;
    protected $eventManager;

    public function __construct(
        \Magento\Framework\App\ObjectManagerFactory $objectManagerFactory,
        \Magento\Backend\App\Action\Context $context,
        \BoostMyShop\OrderPreparation\Model\Config $config,
        \Magento\Framework\Filesystem\DirectoryList $directory,
        \Magento\Framework\Filesystem $filesystem
    )
    {
        $this->objectManagerFactory = $objectManagerFactory;
        $this->_config = $config;
        $this->_directory = $directory;
        $this->_filesystem = $filesystem;
        $this->eventManager = $context->getEventManager();
    }

    protected function getObjectManager()
    {
        if (null == $this->objectManager) {

            $area = FrontNameResolver::AREA_CODE;
            $this->_configScope = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Config\ScopeInterface::class);
            $this->_configScope->setCurrentScope($area);
            $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            /*
            $area = FrontNameResolver::AREA_CODE;
            $this->objectManager = $this->objectManagerFactory->create($_SERVER);
            $appState = $this->objectManager->get('Magento\Framework\App\State');
            $appState->setAreaCode($area);
            $configLoader = $this->objectManager->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
            $this->objectManager->configure($configLoader->load($area));
            */
        }
        return $this->objectManager;
    }

    abstract function getShippingLabelFile($ordersInProgress, $carrierTemplate);

    /**
     * Return an array with 2 keys:
     * file : csv OR pdf file
     * trackings : array with tracking numbers
     *
     * Implementation is done in renderer extending this class
     */
    public function getShippingLabelData($ordersInProgress, $carrierTemplate)
    {
        throw new \Exception('getShippingLabelData not implement for template '.$carrierTemplate->getgetct_type());
    }

    public function supportMultiboxes(){
        return false;
    }

    public function addCustomTabToCarrierTemplate($carrierTemplate, $tabs, $layout)
    {
        //nothing, to override
    }

    public function checkConnection($carrierTemplate)
    {
        //nothing, to override
    }

    public function supportManifestEdi() {
        return false;
    }

    public function sendManifestEdi($carrierTemplate, $manifest) {
        //nothing, to override
    }

    public function canGetRates()
    {
        return false;
    }

    public function getRates($carrierTemplate, $orderInProgress)
    {
        return [];
    }

    public function getTrackingUrl($trackingNumber)
    {
        return false;
    }

    public function supportsReturnLabel()
    {
        return false;
    }

    public function generateReturnLabel($ordersInProgress, $customerAddress)
    {
        return false;
    }
}
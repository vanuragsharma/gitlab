<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManagerFactory;

abstract class Batch extends \Magento\Backend\App\AbstractAction
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    protected $_resultLayoutFactory;
    protected $_backendAuthSession;
    protected $_orderPreparationFactory;
    protected $_orderFactory;
    protected $_inProgressFactory;
    protected $_inProgressItemFactory;
    protected $_configFactory = null;
    protected $_carrierTemplateHelper = null;
    protected $_logger;
    protected $_orderEditor;
    protected $_productFactory;
    protected $_productHelper;
    protected $_preparationRegistry;
    protected $_filesystem;
    protected $_carrierHelper;
    protected $_shipmentRepository;
    protected $objectManagerFactory;
    protected $objectManager;
    protected $_batchFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\OrderPreparation\Model\OrderPreparationFactory $orderPreparationFactory,
        \BoostMyShop\OrderPreparation\Model\InProgressFactory $inProgressFactory,
        \BoostMyShop\OrderPreparation\Model\InProgress\ItemFactory $inProgressItemFactoryFactory,
        \BoostMyShop\OrderPreparation\Model\ConfigFactory $configFactory,
        \BoostMyShop\OrderPreparation\Model\Product $productHelper,
        \BoostMyShop\OrderPreparation\Helper\CarrierTemplate $carrierTemplateHelper,
        \BoostMyShop\OrderPreparation\Model\Order\Editor $orderEditor,
        \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
        \BoostMyShop\OrderPreparation\Helper\Carrier $carrierHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Filesystem $filesystem,
        \BoostMyShop\OrderPreparation\Helper\Logger $logger,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Framework\App\ObjectManagerFactory $objectManagerFactory,
        \BoostMyShop\OrderPreparation\Model\BatchFactory $batchFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_configFactory = $configFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_orderPreparationFactory = $orderPreparationFactory;
        $this->_orderFactory = $orderFactory;
        $this->_inProgressFactory = $inProgressFactory;
        $this->_inProgressItemFactory = $inProgressItemFactoryFactory;
        $this->_carrierTemplateHelper = $carrierTemplateHelper;
        $this->_logger = $logger;
        $this->_orderEditor = $orderEditor;
        $this->_productFactory = $productFactory;
        $this->_productHelper = $productHelper;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_carrierHelper = $carrierHelper;
        $this->_filesystem = $filesystem;
        $this->_shipmentRepository = $shipmentRepository;
        $this->objectManagerFactory = $objectManagerFactory;
        $this->_batchFactory = $batchFactory;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}

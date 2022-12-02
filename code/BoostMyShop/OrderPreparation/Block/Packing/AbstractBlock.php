<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

class AbstractBlock extends \Magento\Backend\Block\Template
{

    protected $_coreRegistry = null;
    protected $_inProgressFactory = null;
    protected $_inProgressItemCollectionFactory = null;
    protected $_product;
    protected $_carrierTemplateHelper = null;
    protected $_preparationRegistry;
    protected $_config = null;
    protected $_productFactory = null;
    protected $_orderItemFactory;
    protected $_warehouses;
    protected $_carrierHelper;
    protected $_policyAuth = null;
    protected $opProduct;
    protected $authSession;
    protected $_batchFactory;
    protected $_templateCollectionFactory;
    protected $_eventManager;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\Registry $registry,
                                \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressFactory,
                                \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\Item\CollectionFactory $inProgressItemCollectionFactory,
                                \BoostMyShop\OrderPreparation\Model\BatchFactory $batchFactory,
                                \BoostMyShop\OrderPreparation\Model\Config\Source\Warehouses $warehouses,
                                \BoostMyShop\OrderPreparation\Model\ProductFactory $product,
                                \BoostMyShop\OrderPreparation\Helper\CarrierTemplate $carrierTemplateHelper,
                                \BoostMyShop\OrderPreparation\Model\Config $config,
                                \BoostMyShop\OrderPreparation\Helper\Carrier $carrierHelper,
                                \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
                                \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \BoostMyShop\OrderPreparation\Model\Product $opProduct,
                                \Magento\Backend\Model\Auth\Session $authSession,
                                \BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate\CollectionFactory $templateCollectionFactory,
                                \Magento\Framework\Event\ManagerInterface $eventManager,
                                array $data = [])
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
        $this->_inProgressFactory = $inProgressFactory;
        $this->_inProgressItemCollectionFactory = $inProgressItemCollectionFactory;
        $this->_product = $product;
        $this->_carrierTemplateHelper = $carrierTemplateHelper;
        $this->_config = $config;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_productFactory = $productFactory;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_warehouses = $warehouses;
        $this->_carrierHelper = $carrierHelper;
        $this->_batchFactory = $batchFactory;

        $this->_policyAuth = $context->getAuthorization();
        $this->opProduct = $opProduct;
        $this->authSession = $authSession;
        $this->_templateCollectionFactory = $templateCollectionFactory;
        $this->_eventManager = $eventManager;
    }

    public function currentOrderInProgress()
    {
        return $this->_coreRegistry->registry('current_packing_order');
    }

    public function hasOrderSelect()
    {
        return ($this->currentOrderInProgress()->getId() > 0);
    }

    public function canDisplay()
    {
        return ($this->hasOrderSelect()
                    && $this->currentOrderInProgress()->getip_status() != \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED
                    && $this->currentOrderInProgress()->getip_status() != \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_PACKED
                );
    }

    public function getCurrentWebsiteId()
    {
        return $this->authSession->getUser()->geterpcloud_website_id();
    }

    public function isBatchEnable()
    {
        return $this->_config->isBatchEnable();
    }

    public function getCurrentBatch()
    {
        if($this->getRequest()->getParam('batch_id')) {
            if ($this->_preparationRegistry->getCurrentBatchId() != $this->getRequest()->getParam('batch_id'))
            {
                $this->_preparationRegistry->changeCurrentBatchId($this->getRequest()->getParam('batch_id'));
            }
        }

        return $this->_batchFactory->create()->load($this->_preparationRegistry->getCurrentBatchId());
    }

    public function hasBatchSelect()
    {
        return ($this->getCurrentBatch()->getId() > 0);
    }
}
<?php

namespace BoostMyShop\OrderPreparation\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;

class Batch extends \Magento\Framework\Model\AbstractModel
{
    protected $_storeManager;
    protected $_userFactory;
    protected $_file;
    protected $_orderPreparation;
    protected $_preparationRegistry;
    protected $_inProgressCollectionFactory;
    protected $_inProgressItemCollectionFactory;
    protected $_configFactory;

    protected $_allItems = null;
    protected $_allOrders = null;
    protected $_objectManager;
    protected $_objectManagerFactory;
    protected $_configScope = null;

    const STATUS_NEW = 'new';
    const STATUS_READY_FOR_LABEL_GENERATION = 'ready_for_label';
    const STATUS_SHIPPING_LABEL_GENERATION = 'label_generation';
    const STATUS_READY = 'ready';
    const STATUS_PRINTED = 'printed';
    const STATUS_INPROGRESS = 'in_progress';
    const STATUS_COMPLETE = 'complete';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\User\Model\User $userFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \BoostMyShop\OrderPreparation\Model\OrderPreparation $orderPreparation,
        \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressCollectionFactory,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\Item\CollectionFactory $inProgressItemCollectionFactory,
        \BoostMyShop\OrderPreparation\Model\ConfigFactory $configFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);
        $this->_storeManager = $storeManager;
        $this->_userFactory = $userFactory;
        $this->_file = $file;
        $this->_orderPreparation = $orderPreparation;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_inProgressCollectionFactory = $inProgressCollectionFactory;
        $this->_inProgressItemCollectionFactory = $inProgressItemCollectionFactory;
        $this->_configFactory = $configFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\OrderPreparation\Model\ResourceModel\Batch');
    }

    public function beforeDelete()
    {
        foreach ($this->getBatchOrders() as $inProgressOrder) {
            $labelPath = $inProgressOrder->getip_shipping_label_pregenerated_label_path();
            if ($labelPath && $this->_file->isExists($labelPath))
                $this->_file->deleteFile($labelPath);
            $inProgressOrder->delete();
        }
        return parent::beforeDelete();
    }

    public function updateOrderCount()
    {
        $this->_allOrders = null;
        $this->setbob_order_count($this->getBatchOrders()->getSize())->save();
        return $this;
    }

    public function updateProductCount()
    {
        $this->_allItems = null;
        $this->setbob_product_count($this->getBatchOrderItems()->getSize())->save();
        return $this;
    }

    public function getBatchOrders()
    {
        if(!$this->_allOrders) {
            $this->_allOrders = $this->_inProgressCollectionFactory->create();
            $this->_allOrders->addbatchFilter($this->getId());
        }

        return $this->_allOrders;
    }

    public function getProcessingOrderCount()
    {
        $collection = $this->_inProgressCollectionFactory->create();
        $collection->addbatchFilter($this->getId());
        $collection->addOrderDetails();
        $collection->addFieldToFilter('status', 'processing');
        return $collection->getSize();
    }

    public function getLabel()
    {
        return "#".$this->getbob_label()." (".$this->getbob_type().") - ".__($this->getbob_status()." - ". $this->getbob_order_count());
    }

    public function getBatchOrderItems()
    {
        if(!$this->_allItems) {
            $collection = $this->_inProgressCollectionFactory->create();
            $collection->addbatchFilter($this->getId());

            $this->_allItems = $this->_inProgressItemCollectionFactory->create();
            $this->_allItems->addFieldToFilter('ipi_parent_id', ['in' => $collection->getAllIds()]);
        }

        return $this->_allItems;
    }

    public function removeOrder($inProgressId)
    {
        $collection = $this->_inProgressCollectionFactory->create();
        $inProgress = $collection->addFieldToFilter('ip_id', $inProgressId)->getfirstItem();
        if(!$inProgress->getId())
            throw new \Exception(__("No inProgress order found for this id"));

        $inProgress->delete();

        $this->updateOrderCount()->updateProductCount();
        if((int)$this->getbob_order_count() == 0)
            $this->delete();
    }

    public function getCurrentWarehouseId()
    {
        return $this->getsetbob_warehouse_id()? $this->getsetbob_warehouse_id() : $this->_preparationRegistry->getCurrentWarehouseId();
    }

    public function getCurrentOperatorId()
    {
        return $this->_preparationRegistry->getCurrentOperatorId();
    }

    public function addOrder($order, $items = [])
    {
        if (count($items) == 0)
            $items = $this->getItemsToShip($order, $this->getCurrentWarehouseId());

        $inprogress = $this->_orderPreparation->addOrder($order, $items, $this->getCurrentOperatorId(), $this->getCurrentWarehouseId());
        $inprogress->setip_batch_id($this->getId())->save();

        $this->updateOrderCount()->updateProductCount();

        return $inprogress;
    }

    public function markAsComplete()
    {
        $this->updateStatus(self::STATUS_COMPLETE);
    }

    public function updateStatus($status)
    {
        $statusLabels = [
            'new' => __('New'),
            'ready_for_label' => __('Ready for labels generation'),
            'label_generation' => __('Labels generated'),
            'ready' => __('Ready'),
            'printed' => __('Printed'),
            'in_progress' => __('In progress'),
            'complete' => __('Complete')
        ];

        if($this->_configFactory->create()->isOrganizerModuleInstall() && $this->getbob_status() != $status){
            $title = __('Status updated');
            $comments = __("Status updated from '%1' to '%2'", $statusLabels[$this->getbob_status()], $statusLabels[$status]);

            $this->addOrganizer($title, $comments);
        }

        $this->setbob_status($status)->save();
    }

    public function updatedProgress($type)
    {
        $this->_allOrders = null;
        $orderCount = $this->getBatchOrders()->getSize();

        if($orderCount == 0)
            return;

        $successCount = 0;
        switch ($type)
        {
            case "label":
                foreach ($this->getBatchOrders() as $ipOrder)
                {
                    if($ipOrder->getip_shipping_label_pregenerated_status() == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPING_LABEL_PREGENERATED_OK)
                    {
                        $successCount++;
                    }
                }
                break;
            case "shipment":

                foreach ($this->getBatchOrders() as $ipOrder)
                {
                    $status = $ipOrder->getip_status();
                    if($status == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED || $status == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_PACKED)
                    {
                        $successCount++;
                    }
                }
                break;
        }
        $successRate = $successCount * 100 / $orderCount;
        $this->setbob_progress($successRate)->save();
    }

    public function addOrganizer($title, $comments)
    {
        $userId = $this->getCurrentOperatorId() ? : -1;
        $organizer = $this->getObjectManager()->create('BoostMyShop\Organizer\Model\Organizer');
        $organizer->seto_author_user_id($userId)
            ->seto_title($title)
            ->seto_comments($comments)
            ->seto_category()
            ->seto_status('New')
            ->seto_object_type(\BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_BATCH)
            ->seto_object_id($this->getbob_id())
            ->seto_created_at($this->getObjectManager()->create('Magento\Framework\Stdlib\DateTime\DateTime')->gmtDate())
            ->seto_object_description('Batch '.$this->getbob_label())
            ->save();
        
        return $organizer;
    }

    protected function getObjectManager()
    {
        if (null == $this->_objectManager) {
            $area = FrontNameResolver::AREA_CODE;
            $this->_configScope = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Config\ScopeInterface::class);
            $this->_configScope->setCurrentScope($area);
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }
        return $this->_objectManager;
    }
}

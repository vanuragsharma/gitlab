<?php

namespace BoostMyShop\OrderPreparation\Helper;

use Magento\Backend\App\Area\FrontNameResolver;

class PregenerateLabel
{

    protected $_scopeConfig;
    protected $_batchFactory;
    protected $_configFactory;
    protected $_convertOrder;
    protected $mathRandom;

    protected $_currentBatch = null;
    protected $_isOrganizerModuleInstalled = false;
    protected $_results = [];
    protected $_batchResults = [];
    protected $_objectManager = null;
    protected $_configScope = null;

    public function __construct
    (
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \BoostMyShop\OrderPreparation\Model\BatchFactory $batchFactory,
        \BoostMyShop\OrderPreparation\Model\ConfigFactory $configFactory
    )
    {
        $this->mathRandom = $mathRandom;
        $this->_convertOrder = $convertOrder;
        $this->_scopeConfig = $scopeConfig;
        $this->_batchFactory = $batchFactory;
        $this->_configFactory = $configFactory;
    }

    public function process()
    {
        $this->_isOrganizerModuleInstalled = $this->_configFactory->create()->isOrganizerModuleInstall();

        $collection = $this->_batchFactory->create()->getCollection()->addStatusFilter(\BoostMyShop\OrderPreparation\Model\Batch::STATUS_READY_FOR_LABEL_GENERATION);
        foreach ($collection as $batch)
        {
            try
            {
                $this->processBatch($batch);
                $batchId = $batch->getId();
                $success = 1;

                //If too many labels have not been generated (> 15%) set error flag to true
                if($this->_batchResults[$batchId]['total'] > 0 && (($this->_batchResults[$batchId]['error'] / $this->_batchResults[$batchId]['total']) * 100) >= 15)
                    $success = 0;

                $this->_results[] = ['success' => $success, 'msg' => __(
                    'Batch %1 processed : Orders: %2, Success: %3, Error: %4, Skipped: %5',
                    $batch->getbob_label(),
                    $this->_batchResults[$batchId]['total'],
                    $this->_batchResults[$batchId]['success'],
                    $this->_batchResults[$batchId]['error'],
                    $this->_batchResults[$batchId]['skipped']
                )];
            }
            catch (\Exception $e)
            {
                $this->_results[] = ['success' => 0, 'msg' => $e->getMessage()];
            }
        }

        return $this->_results;
    }


    public function processBatch($batch)
    {
        $this->_currentBatch = $batch;
        $this->_currentBatch->updateStatus(\BoostMyShop\OrderPreparation\Model\Batch::STATUS_SHIPPING_LABEL_GENERATION);

        $batchOrders = $this->_currentBatch->getBatchOrders();
        $this->_batchResults[$batch->getId()] = ['total' => $batchOrders->getSize(), 'success' => 0, 'error' => 0, 'skipped' => 0];
        foreach ($batchOrders as $inProgress)
        {
            if($this->canPregenerateForBatchType($batch, $inProgress))
            {
                if($this->pregenerateLabel($inProgress))
                    $this->_batchResults[$batch->getId()]['success'] ++;
                else
                    $this->_batchResults[$batch->getId()]['error'] ++;
            } else {
                $this->_batchResults[$batch->getId()]['skipped'] ++;
            }

            $this->_currentBatch->updatedProgress("label");
        }

        $this->_currentBatch->updateStatus(\BoostMyShop\OrderPreparation\Model\Batch::STATUS_READY);
    }

    public function canPregenerateForBatchType($batch, $inProgress)
    {
        $carrierTemplate = $inProgress->getCarrierTemplate();
        if($carrierTemplate && $carrierTemplate->getct_disable_labels_pregeneration())
        {
            $disableSettings = unserialize($carrierTemplate->getct_disable_labels_pregeneration());
            foreach($disableSettings as $setting)
            {
                if($setting == $batch->getbob_type())
                    return false;
            }
        }

        return true;
    }

    public function pregenerateLabel($inProgress)
    {
        $carrierTemplate = $inProgress->getCarrierTemplate();
        if(!$carrierTemplate)
        {
            $this->markInProgressAsError($inProgress, 'Shipping label pre-generation failed : No shipping label template associated to shipping method '.$inProgress->getOrder()->getShippingMethod());
            return false;
        }

        try
        {
            $shipment = $this->getDummyShippment($inProgress);
            $inProgress->setForceShipment($shipment);
            $inProgress->setip_dummy_shipment_increment_id($shipment->getincrement_id());
            $labelData = $carrierTemplate->getShippingLabelData([$inProgress], $carrierTemplate);

            if(!$labelData['file'])
                throw new \Exception(__("Shipping label couldn't be generated from transporter API"));

            $fileNameWithPath = $this->getShippingLabelPath($inProgress);
            $this->createFile($fileNameWithPath, $labelData['file']);

            if(!file_exists($fileNameWithPath))
                throw new \Exception(__("No shipping label file found on server"));

            $trackings = implode(",", $labelData['trackings']);
            $inProgress->setip_shipping_label_pregenerated_label_path($fileNameWithPath);
            $inProgress->setip_shipping_label_pregenerated_tracking($trackings);

            $this->markInProgressAsSuccess($inProgress);
            return true;
        }
        catch(\Exception $e) {
            $this->markInProgressAsError($inProgress, $e->getMessage());
            return false;
        }
    }


    public function getShippingLabelPath($inProgress)
    {
        $websiteId = $inProgress->getStore()->getWebsiteId();
        $documentFileName = str_replace('{increment_id}', $inProgress->getOrder()->getIncrementId(), $inProgress->getCarrierTemplate()->getct_export_file_name());

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_erpCloudStorageHelper = $objectManager->get(\BoostMyShop\ErpCloudStorage\Helper\Storage::class);

        return $_erpCloudStorageHelper->getFilePath($websiteId, "pregenerated", $documentFileName, $inProgress->getOrder()->getCreatedAt());
    }

    public function createFile($fileNameWithPath, $content)
    {
        try {
            file_put_contents($fileNameWithPath, $content);
        }catch(Exception $e){
            throw new \Exception(__("Error while creating shipping label file on server : %1", $e->getMessage()));
        }
    }

    protected function markInProgressAsError($inProgress, $errorMsg)
    {
        try
        {
            $currentOrder = $inProgress->getOrder();
            $statusCodeForShippingLabelError = \BoostMyShop\OrderPreparation\Setup\UpgradeData::ORDER_STATUS_SHIPPING_LABEL_ERROR_CODE;

            //Set shipping label error order status
            if($currentOrder->getStatus() !== $statusCodeForShippingLabelError)
            {
                $currentOrder->setStatus($statusCodeForShippingLabelError)->save();
            }

            //Add organizer to inform order has been holded due to shipping label pre-generation error
            if($this->_isOrganizerModuleInstalled)
                $this->addOrganizer($currentOrder, $errorMsg);
        }
        catch (\Exception $e)
        {
            throw new \Exception($e->getMessage());
        }

        //Remove order from batch
        $this->_currentBatch->removeOrder($inProgress->getId());

    }

    protected function addOrganizer($order, $errorMsg)
    {
        $organizer = $this->getObjectManager()->create('BoostMyShop\Organizer\Model\Organizer');
        $organizer->seto_author_user_id(-1)
            ->seto_title(__('Order Holded'))
            ->seto_comments($errorMsg)
            ->seto_category()
            ->seto_status('New')
            ->seto_object_type(\BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_ORDER)
            ->seto_object_id($order->getId())
            ->seto_created_at($this->getObjectManager()->create('Magento\Framework\Stdlib\DateTime\DateTime')->gmtDate())
            ->seto_object_description('Order #'.$order->getincrement_id())
            ->save();

        return $organizer;
    }

    protected function markInProgressAsSuccess($inProgress)
    {
        $inProgress->setip_shipping_label_pregenerated_status(\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPING_LABEL_PREGENERATED_OK)->save();
    }

    public function getDummyShippment($orderInProgress)
    {
        if($orderInProgress->getip_total_weight()<=0)
            $orderInProgress->setip_total_weight(1)->save();

        $order = $orderInProgress->getOrder();
        //$order->setShippingMethod($this->_shippingMethod);

        $shipment = $this->_convertOrder->toShipment($order);
        $weight = $orderInProgress->getip_total_weight();
        $boxes = json_decode($orderInProgress->getip_boxes(), true);

        if(count($boxes)==1){
            $packages = array_map(function($boxes) {
                return array(
                    'weight' => $boxes['total_weight'],
                    'length' => $boxes['parcel_length'],
                    'height' => $boxes['parcel_height'],
                    'width' => $boxes['parcel_width'],
                );
            }, $boxes);
        }else
            $packages = array(array('weight' => '', 'length' => '', 'width' => '', 'height' => ''));

        if($weight <= 0) {
            throw new \Exception(__('weight must be > 0'));
        }
        $shipment->setincrement_id($order->getincrement_id().$this->getRandomNumber(1, 999));
        $shipment->settotal_weight($weight);
        $shipment->setPackages($packages);
        $shipment->setcreated_at(date("Y-m-d h:i:s"));

        return $shipment;
    }

    public function getRandomNumber($min = 0, $max = null)
    {
        return $this->mathRandom->getRandomNumber($min, $max);
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

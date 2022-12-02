<?php

namespace BoostMyShop\OrderPreparation\Helper;

class Manifest
{

    protected $_orderShipmentCollectionFactory;
    protected $_resourceConnection;
    protected $_date;
    protected $_configFactory;
    protected $_manifestFactory;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \BoostMyShop\OrderPreparation\Model\ConfigFactory $configFactory,
        \BoostMyShop\OrderPreparation\Model\ManifestFactory $manifestFactory
    )
    {
        $this->_orderShipmentCollectionFactory = $orderShipmentCollectionFactory;
        $this->_resourceConnection = $resourceConnection;
        $this->_date = $date;
        $this->_configFactory = $configFactory;
        $this->_manifestFactory = $manifestFactory;
    }

    public function listShipments($carrier, $warehouseId = null, $fromDate = null)
    {
        $shipmentCollection = $this->_orderShipmentCollectionFactory->create()
                                ->addFieldToFilter('manifest_id', array('null' => true));

        if($this->_configFactory->create()->isAdvancedstockModuleInstall())
            $shipmentCollection->addFieldToFilter('warehouse_id', $warehouseId);
        if(!is_null($fromDate))
            $shipmentCollection->addFieldToFilter('main_table.created_at', ['from' => $this->_date->gmtDate('Y-m-d H:i:s', strtotime($fromDate))]);

        $shipmentCollection->getSelect()->join(
            ["so" => "sales_order"],
            "main_table.order_id = so.entity_id AND so.shipping_method LIKE '".$carrier."%'",
            ["order_increment_id"=>"so.increment_id",
                'customer_firstname',
                'customer_lastname'
            ]);

        return $shipmentCollection;
    }

    public function createManifest($carrier, $warehouseId, $shipmentIds)
    {
        if(count($shipmentIds) == 0)
            throw new \Exception(__("Shipments not found for this manifest"));

            $manifest = $this->_manifestFactory->create()->setbom_date($this->_date->gmtDate())
                                                        ->setbom_warehouse_id($warehouseId)
                                                        ->setbom_carrier($carrier)
                                                        ->setbom_shipment_count(count($shipmentIds))
                                                        ->setbom_edi_status(\BoostMyShop\OrderPreparation\Model\Manifest::STATUS_CREATED)
                                                        ->save();
            $manifestId = $manifest->getId();

            if($manifestId)
                $this->setManifestIdInShipment($manifestId, $shipmentIds);

        return $this;
    }

    public function setManifestIdInShipment($manifestId, $shipmentIds = [])
    {
        $connection = $this->_resourceConnection->getConnection();
        $sql = 'UPDATE '.$connection->getTableName('sales_shipment'). ' SET `manifest_id` = "'.$manifestId.'" WHERE `entity_id` IN ('.implode(',', $shipmentIds).')';
        $connection->query($sql);
    }
}
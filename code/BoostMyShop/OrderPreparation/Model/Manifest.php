<?php

namespace BoostMyShop\OrderPreparation\Model;


class Manifest extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_CREATED = 'created';
    const STATUS_SENT = 'sent';

    protected $_shipmentCollectionFactory;


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        array $data = []
    )
    {
        $this->_shipmentCollectionFactory = $shipmentCollectionFactory;
        parent::__construct($context, $registry, null, null, $data);
    }

    protected function _construct()
    {
        $this->_init('BoostMyShop\OrderPreparation\Model\ResourceModel\Manifest');
    }

    public function getShipments($manifestId)
    {
        $collection = $this->_shipmentCollectionFactory->create()->addFieldToFilter('manifest_id', $manifestId);

        return $collection;
    }

}

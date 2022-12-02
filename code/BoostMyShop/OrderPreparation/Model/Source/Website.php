<?php

namespace BoostMyShop\OrderPreparation\Model\Source;

class Website implements \Magento\Framework\Option\ArrayInterface
{
    protected $_websiteCollectionFactory;


    public function __construct(\Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory)
    {
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
    }

    public function toOptionArray()
    {
        $options = array();

        foreach($this->_websiteCollectionFactory->create()->setOrder('name') as $item)
        {
            $options[] = array('value' => $item->getId(), 'label' => $item->getName());
        }

        return $options;
    }

    public function toArray()
    {
        $options = array();
        $collection = $this->_websiteCollectionFactory->create();

        foreach($collection as $item)
        {
            $options[$item->getId()] = $item->getName();
        }

        return $options;

    }

}

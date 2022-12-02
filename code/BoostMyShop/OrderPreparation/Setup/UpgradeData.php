<?php

namespace BoostMyShop\OrderPreparation\Setup;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;


class UpgradeData implements UpgradeDataInterface
{
    const ORDER_STATUS_SHIPPING_LABEL_ERROR_CODE = 'shipping_label_error';
    const ORDER_STATUS_SHIPPING_LABEL_ERROR_LABEL = 'Shipping Label Error';

    private $_statusFactory;
    private $_statusResourceFactory;

    public function __construct(
        \Magento\Sales\Model\Order\StatusFactory $statusFactory,
        \Magento\Sales\Model\ResourceModel\Order\StatusFactory $statusResourceFactory
    ) {
        $this->_statusFactory = $statusFactory;
        $this->_statusResourceFactory = $statusResourceFactory;
    }


    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $statusResource = $this->_statusResourceFactory->create();

        if (version_compare($context->getVersion(), '0.0.33') < 0)
        {
            $status = $this->_statusFactory->create();
            $status->setData([
                'status' => self::ORDER_STATUS_SHIPPING_LABEL_ERROR_CODE,
                'label' => self::ORDER_STATUS_SHIPPING_LABEL_ERROR_LABEL
            ]);
            try {
                $statusResource->save($status);
            } catch (AlreadyExistsException $exception) {
                return;
            }
            $status->assignState(\Magento\Sales\Model\Order::STATE_PROCESSING, false, false);

        }

        $setup->endSetup();
    }
}

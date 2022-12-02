<?php

namespace BoostMyShop\Supplier\Model\ResourceModel;


class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_purchase_order', 'po_id');
    }

    public function getNextReference($type = 'PO', $websiteId)
    {
        if (!$type)
            $type = 'po';
        $prefix = $type.'-'.date('Ymd').'-';

        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable(), array(new \Zend_Db_Expr('max(po_reference) as maxReference')))
            ->where('po_reference like "' .$prefix. '%" ');
        if ($websiteId)
            $select->where('po_website_id = '.$websiteId);
        $result = $connection->fetchOne($select);

        if (!$result)
            $result = $prefix.'0001';
        else
            $result++;

        return strtoupper($result);
    }

    public function copyChangeRateToProducts($orderId, $changeRate)
    {
        $connection = $this->getConnection();
        $data['pop_change_rate'] = $changeRate;
        $condition = 'pop_po_id = '.$orderId;
        $connection->update($this->getTable('bms_purchase_order_product'), $data, $condition);

        return $this;
    }

    public function updateDeliveryProgress($orderId)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getTable('bms_purchase_order_product'), array(new \Zend_Db_Expr('SUM(pop_qty_received >= pop_qty and pop_qty > 0) / SUM(pop_qty > 0) * 100 as delivery_progress')))
            ->where('pop_po_id = ' .$orderId);
        $result = $connection->fetchOne($select);

        $data['po_delivery_progress'] = $result;
        if ($result == 100)
            $data['po_status'] = \BoostMyShop\Supplier\Model\Order\Status::complete;

        $condition = 'po_id = '.$orderId;
        $connection->update($this->getMainTable(), $data, $condition);

        return $this;
    }

    public function updateMissingPrices($orderId)
    {
        if ($orderId)
        {
            $select = $this->getConnection()
                ->select()
                ->from($this->getTable('bms_purchase_order_product'), array(new \Zend_Db_Expr('count(*)')))
                ->where('pop_po_id = ' .$orderId)
                ->where('pop_price = 0 OR pop_price IS NULL');
            $result = $this->getConnection()->fetchOne($select);

            $condition = 'po_id = '.$orderId;
            $this->getConnection()->update($this->getMainTable(), ['po_missing_price' => $result], $condition);
        }
        return 0;
    }

    public function updateTotals($order)
    {
        $data = [];

        $data['po_subtotal'] = $this->getProductsSubTotal($order->getId(), false);
        $data['po_subtotal_base'] = $this->getProductsSubTotal($order->getId(), true);

        $data['po_tax'] = $this->getProductsTax($order, false) + ($order->getpo_shipping_cost() + $order->getpo_additionnal_cost()) / 100 * $order->getpo_tax_rate();

        $data['po_tax_base'] = $this->getProductsTax($order, true) + ($order->getpo_shipping_cost_base() + $order->getpo_additionnal_cost_base()) / 100 * $order->getpo_tax_rate();
        $data['po_grandtotal'] = $data['po_subtotal'] - $order->getGlobalDiscountAmount($data['po_subtotal']) + $order->getpo_shipping_cost() + $order->getpo_additionnal_cost() + $data['po_tax'];
        $data['po_grandtotal_base'] = $data['po_subtotal_base'] - $order->getGlobalDiscountBaseAmount($data['po_subtotal_base']) + $order->getpo_shipping_cost_base() + $order->getpo_additionnal_cost_base() + $data['po_tax_base'];

        $this->getConnection()->update($this->getMainTable(), $data, 'po_id = '.$order->getId());

        return $this;
    }

    protected function getProductsSubTotal($orderId, $base = false)
    {
        $columnName = ($base ? 'pop_subtotal_base' : 'pop_subtotal');
        $select = $this->getConnection()
                        ->select()
                        ->from($this->getTable('bms_purchase_order_product'), array(new \Zend_Db_Expr('SUM('.$columnName.') as subtotal')))
                        ->where('pop_po_id = ' .$orderId);
        $result = $this->getConnection()->fetchOne($select);
        return $result;
    }

    protected function getProductsTax($order, $base = false)
    {
        $columnName = ($base ? 'pop_tax_base' : 'pop_tax');
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('bms_purchase_order_product'), array(new \Zend_Db_Expr('SUM('.$columnName.') as subtotal')))
            ->where('pop_po_id = ' .$order->getId());
        $result = $this->getConnection()->fetchOne($select);

        //deduct global discount
        if ($order->getpo_global_discount() > 0)
        {
            $discountedTax = ($result / 100 * $order->getpo_global_discount());
            $result = $result - $discountedTax;
        }


        return $result;
    }

    public function updateExtendedCostForItems($orderId, $method, $unitValue, $changeRate)
    {

        switch($method)
        {
            case 'quantity':
                $connection = $this->getConnection();
                $data['pop_extended_cost'] = $unitValue;
                $data['pop_extended_cost_base'] = $data['pop_extended_cost'] * $changeRate;
                $connection->update($this->getTable('bms_purchase_order_product'), $data, 'pop_po_id = '.$orderId);
                break;
            case 'value':
                $connection = $this->getConnection();
                $data['pop_extended_cost'] = new \Zend_Db_Expr('pop_price * '.$unitValue);
                $data['pop_extended_cost_base'] = new \Zend_Db_Expr('pop_price * '.$unitValue.' * '.$changeRate);
                $connection->update($this->getTable('bms_purchase_order_product'), $data, 'pop_po_id = '.$orderId);
                break;
            case 'weight':
                $connection = $this->getConnection();
                $collection = $this->getProducts($orderId);
                foreach ($collection as $product) {
                    $data['pop_extended_cost'] = $product['product_weight'] * $unitValue * 100; //because unitValue is for 0.01 weight
                    $data['pop_extended_cost_base'] = $product['product_weight'] * $unitValue * 100 * $changeRate;
                    $connection->update($this->getTable('bms_purchase_order_product'), $data, 'pop_id = '.$product['pop_id']);
                }
                break;
        }
        return $this;
    }

    public function getProducts($orderId){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $entityTypeId = $objectManager->get(\Magento\Eav\Model\Config::class)->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)->getEntityTypeId();
        $weightAttribute = $objectManager->get(\Magento\Eav\Model\Entity\Attribute::class)->loadByCode($entityTypeId, 'weight');
        $collection = $this->getConnection()
                        ->select()
                        ->from($this->getTable('bms_purchase_order_product'), array(new \Zend_Db_Expr('pop_id')))
                        ->joinLeft(
                            ["attrw" => $this->getTable('catalog_product_entity_'.$weightAttribute->getBackendType())],
                            "pop_product_id = attrw.entity_id AND
                            attrw.attribute_id = ".$weightAttribute->getAttributeId(). " AND
                            attrw.store_id = ".\Magento\Store\Model\Store::DEFAULT_STORE_ID,
                            ["product_weight"=>"attrw.value"])
                        ->where('pop_po_id = ' .$orderId);

        $products = $this->getConnection()->fetchAll($collection);
        return $products;
    }

}

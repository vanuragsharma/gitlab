<?php

namespace BoostMyShop\Supplier\Model\Order;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class FileExport
{

    protected $_eventManager;

    public function __construct(
        \Magento\Framework\Event\Manager $eventManager
    )
    {
        $this->_eventManager = $eventManager;
    }

    public function getFileName($po, $supplier = false)
    {
        $fileName = null;

        if (is_array($po) || $po instanceof \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection)
        {
            if ($supplier->getsup_file_name())
            {
                $fileName = $supplier->getsup_file_name();
                $fileName = str_replace('{reference}', 'orders', $fileName);
            }
            else
            {
                return 'purchase_orders.csv';
            }
        }
        else
        {
            if ($po->getSupplier()->getsup_file_name())
            {
                $fileName = $po->getSupplier()->getsup_file_name();
                $fileName = str_replace('{reference}', $po->getpo_reference(), $fileName);
            }
            else
            {
                return 'po_'.$po->getpo_reference().'.csv';
            }
        }

        return $fileName;
    }

    public function getFileContent($po, $supplier = false)
    {
        $supplier = null;

        if(!$supplier && $po instanceof \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection)
            $supplier = $po->getFirstItem()->getSupplier();
        else if (is_array($po)) {
            $singlePo = reset($po);
            $supplier = $singlePo->getSupplier();
        }
        else {
            $supplier = $po->getSupplier();
            $po = [$po];
        }


        if (!$supplier->getsup_file_header() && !$supplier->getsup_file_product())
            return $this->getDefaultFileContent($po);

        $content = '';

        $header = $this->transformTemplate($supplier->getsup_file_header(), $po);
        if ($header)
            $content .= $header."\r\n";

        foreach($po as $purchaseOrder)
        {
            if ($this->transformTemplate($supplier->getsup_file_order_header(), $purchaseOrder))
                $content .= $this->transformTemplate($supplier->getsup_file_order_header(), $purchaseOrder)."\r\n";

            foreach($purchaseOrder->getAllItems() as $item)
                $content .= $this->transformTemplate($supplier->getsup_file_product(), $purchaseOrder, $item)."\r\n";

            if ($this->transformTemplate($supplier->getsup_file_order_footer(), $purchaseOrder))
                $content .= $this->transformTemplate($supplier->getsup_file_order_footer(), $purchaseOrder)."\r\n";
        }

        if ($this->transformTemplate($supplier->getsup_file_footer(), $po))
            $content .= $this->transformTemplate($supplier->getsup_file_footer(), $po)."\r\n";

        $content = $this->removeEmptyCodes($content);

        return $content;
    }

    public function transformTemplate($template, $po, $pop = null)
    {
        if (!is_array($po) && !$po instanceof \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection)
        {
            foreach($this->getCodes($po, $pop) as $k => $v)
            {
                $template = str_replace("{".$k."}", $v, $template);
            }
        }
        return $template;
    }

    /**
     * Return available codes
     *
     * @param $po
     * @param $pop
     * @return array
     */
    public function getCodes($po, $pop = null)
    {
        $codes = [];

        //po
        foreach($po->getData() as $k => $v)
        {
            if (!is_array($v) && !is_object($v))
                $codes['po.'.$k] = $v;
        }

        //supplier
        foreach($po->getSupplier()->getData() as $k => $v)
        {
            if (!is_array($v) && !is_object($v))
                $codes['supplier.'.$k] = $v;
        }

        //pop
        if ($pop)
        {
            foreach($pop->getData() as $k => $v)
            {
                if (!is_array($v) && !is_object($v))
                    $codes['item.'.$k] = $v;
            }
        }

        //product
        if ($pop && $pop->getProduct())
        {
            foreach($pop->getProduct()->getData() as $k => $v)
            {
                if (!is_array($v) && !is_object($v))
                    $codes['product.'.$k] = $v;
            }

            $codes['product.pop_unit_price_with_cost'] = $pop->getUnitPriceWithCost();
            $codes['product.pop_grand_total_with_cost_and_discount'] = $pop->getGrandTotalWithCostsAndDiscount();
        }

        //raise an event so other modules (mostly dropship one) can append their own codes
        $obj = new \Magento\Framework\DataObject();
        $obj->setCodes($codes);
        $this->_eventManager->dispatch('bms_supplier_order_export_codes', ['po' => $po, 'pop' => $pop, 'obj' => $obj]);
        $codes = $obj->getCodes();

        return $codes;
    }


    public function getDefaultFileContent($poArray)
    {
        $content = '';

        $columns = [
                    'po_reference' => 'po.po_reference',
                    'pop_sku' => 'item.pop_sku',
                    'pop_name' => 'item.pop_name',
                    'pop_supplier_sku' => 'item.pop_supplier_sku',
                    'pop_qty' => 'item.pop_qty',
                    'pop_qty_pack' => 'item.pop_qty_pack',
                    'pop_qty_received' => 'item.pop_qty_received',
                    'pop_price' => 'item.pop_price',
                    'pop_discount_percent' => 'item.pop_discount_percent',
                    'pop_tax_rate' => 'item.pop_tax_rate',
                    'pop_tax' => 'item.pop_tax',
                    'pop_subtotal' => 'item.pop_subtotal',
                    'pop_grandtotal' => 'item.pop_grandtotal',
                    'pop_eta' => 'item.pop_eta'
                    ];

        $header = [];
        foreach($columns as $columnName => $columnCode)
            $header[] = '"'.str_replace('pop_', '', $columnName.'"');
        $content .= implode(',', $header)."\r\n";


        foreach($poArray as $po)
        {
            foreach($po->getAllItems() as $item)
            {
                $data = $this->getCodes($po, $item);
                $line = [];
                foreach($columns as $columnName => $columnCode)
                {
                    $line[] .= '"'.(isset($data[$columnCode]) ? $data[$columnCode] : '').'"';
                }

                $content .= implode(',', $line)."\r\n";
            }
        }

        return $content;
    }


    public function removeEmptyCodes($content)
    {
        $matches = [];
        preg_match_all('/\{([^\}]*)\}/', $content, $matches, PREG_OFFSET_CAPTURE);
        if (isset($matches[0]))
        {
            foreach($matches[0] as $occurence)
            {
                if (isset($occurence[0]))
                    $content = str_replace($occurence[0], '', $content);
            }
        }

        return $content;
    }

}

<?php

namespace BoostMyShop\Supplier\Model\Order\Reception;


class ImportHandler extends \Magento\Framework\Model\AbstractModel
{
    protected $csvProcessor;
    protected $_eventManager;
    protected $_backendAuthSession;
    protected $_product;
    protected $_orderFactory;

    protected $fieldsIndexes = [];
    protected $_products = [];
    protected $_results = ['success' => 0, 'errors' => []];

    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Catalog\Model\Product $product,
        \BoostMyShop\Supplier\Model\OrderFactory $orderFactory
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->_eventManager = $eventManager;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_product = $product;
        $this->_orderFactory = $orderFactory;
    }

    public function importFromCsvFile($poId, $path, $delimiter = ';')
    {
        $order = $this->_orderFactory->create()->load($poId);

        //perform checks
        $this->csvProcessor->setDelimiter($delimiter);
        $rows = $this->csvProcessor->getData($path);

        if (!isset($rows[0]))
            throw new \Exception(__('The file is empty'));
        $columns = $rows[0];
        $this->checkColumns($columns);

        //import rows
        foreach ($rows as $rowIndex => $rowData) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }

            if ($this->_importRow($order, $rowData))
                $this->_results['success']++;
        }

        if(!count($this->_products) > 0 && !count($this->_results['errors']) > 0)
            throw new \Exception('No product found in file');

        if($this->_results['success'] > 0)
            $order->processReception($this->getUserName(), $this->_products);

        return $this->_results;
    }

    public function getUserName()
    {
        $userName = '?';
        if ($this->_backendAuthSession->isLoggedIn())
            $userName =  $this->_backendAuthSession->getUser()->getUsername();

        return $userName;
    }

    public function checkColumns($columns)
    {
        $mandatory = [
            0 => 'sku'
        ];

        for($i=0;$i<count($columns);$i++)
        {
            $this->fieldsIndexes[$columns[$i]] = $i;
        }

        foreach($mandatory as $field)
        {
            if (!isset($field))
                throw new \Exception(__('Mandatory column %1 is missing', $field));
        }

        return true;
    }

    protected function _importRow($order, $rowData)
    {
        try {
            //check that product ID exists
            $sku = '';
            if (isset($this->fieldsIndexes['sku']))
                $sku = $rowData[$this->fieldsIndexes['sku']];
            if (!$sku)
                return false;

            $productId = $this->getProductIdBySku($sku, $rowData);
            if (!$productId)
                throw new \Exception(__('Product with sku %1 not found', $sku));

            //check that product is in PO products
            if (!$this->productExistsInPo($productId, $order))
                throw new \Exception(__('Product with ID %1 not found in PO products', $productId));

            $qty = (isset($this->fieldsIndexes['qty']) && $this->fieldsIndexes['qty']) ? $rowData[$this->fieldsIndexes['qty']] : 1;
            $qtyPack = (isset($this->fieldsIndexes['qty_pack']) && $this->fieldsIndexes['qty_pack']) ? $rowData[$this->fieldsIndexes['qty_pack']] : 1;

            if (!isset($this->_products[$productId])) {
                $this->_products[$productId] = [
                    'qty_pack' => (int)$qtyPack,
                    'qty' => (int)$qty
                ];
            } else {
                $this->_products[$productId]['qty'] += $qty;
            }

            $obj = new \Magento\Framework\DataObject();
            $obj->setProducts($this->_products);
            $this->_eventManager->dispatch('bms_supplier_after_purchase_order_reception_row_import', ['obj' => $obj, 'fields_indexes' => $this->fieldsIndexes, 'row_data' => $rowData, 'product_id' => $productId]);
            $this->_products = $obj->getProducts();
        }
        catch (\Exception $e)
        {
            $this->_results['errors'][] = $e->getMessage();
            return false;
        }

        return true;
    }

    public function getProductIdBySku($sku, $rowData)
    {
        return $this->_product->getIdBySku($sku);
    }

    public function productExistsInPo($productId, $order)
    {
        foreach ($order->getAllItems() as $item) {
            if((int) $item['pop_product_id'] == $productId)
                return true;
        }

        return false;
    }
}

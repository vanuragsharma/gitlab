<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake;


class ProductInformation extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake
{

    protected $_stockTake;

    public function execute()
    {
        $result = $this->_resultJsonFactory->create();

        $data = ['success' => 1, 'msg' => "", "product" => []];

        try
        {
            $stockTakeId = $this->getRequest()->getParam('st_id');
            $stockTake = $this->_stockTakeFactory->create()->load($stockTakeId);

            $barcode = $this->getRequest()->getParam('barcode');
            $stockTakeId = $this->getRequest()->getParam('st_id');

            $productId = $this->_productInformation->getIdFromBarcode($barcode);
            if (!$productId)
                throw new \Exception('Product with barcode '.$barcode.' not found');
            $product = $this->_product->load($productId);

            $stockTakeItem = $this->_stockTakeItemCollectionFactory->create()
                                            ->addStockTakeFilter($stockTakeId)
                                            ->addSkuFilter($product->getSku())
                                            ->getFirstItem();
            if (!$stockTakeItem->getId())
            {
                //if stock take item is missing, we add product to stock take
                $stockTakeItemData = [
                    'sku' => $product->getSku(),
                    'name' => $product->getName(),
                    'qty' => 0,         //qty 0, because if there was some qty, then we would have the record already
                    'location' => '',
                    'manufacturer' => ''
                ];
                $stockTake->addItems([$stockTakeItemData]);

                //Reload item
                $stockTakeItem = $this->_stockTakeItemCollectionFactory->create()
                    ->addStockTakeFilter($stockTakeId)
                    ->addSkuFilter($product->getSku())
                    ->getFirstItem();

            }

            $data['product'] = [
                'barcode'       => $barcode,
                'product_id'    => $productId,
                'name'          => $product->getName(),
                'image_url'     => $this->_productInformation->getImage($product),
                'sku'           => $product->getSku(),
                'expected_qty'  => $stockTakeItem->getstai_expected_qty(),
                'scanned_qty'   => $stockTakeItem->getstai_scanned_qty(),
                'location'      => $stockTakeItem->getstai_location(),
                'stock_take_item_id' => $stockTakeItem->getstai_id(),
                'remaining_qty'      => max($stockTakeItem->getstai_expected_qty() - $stockTakeItem->getstai_scanned_qty(), 0)
            ];

        }
        catch(\Exception $ex)
        {
            $data['success'] = 0;
            $data['msg'] = $ex->getMessage();
        }

        return $result->setData($data);
    }

}

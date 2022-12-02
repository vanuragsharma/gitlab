<?php
namespace Ennovations\Mymodule\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
//use Magento\Catalog\Model\ProductFactory ProductFactory;
use Magento\Framework\App\ResourceConnection;

class Index extends Action
{
    protected $resultPageFactory;
    //protected $productFactory;
    protected $resourceConnection;

    public function __construct(Context $context, PageFactory $resultPageFactory, ResourceConnection $resourceConnection) //ProductFactory $productFactory)

    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resourceConnection = $resourceConnection;
        //$this->productFactory = $productFactory;

    }

    public function execute()
    {
        @ini_set('memory_limit', '-1');
        @ini_set('max_execution_time', '180000');

        $connection = $this
            ->resourceConnection
            ->getConnection();
        // $table is table name
        $catalog_product_entity_varchar = $this
            ->resourceConnection
            ->getTableName('catalog_product_entity_varchar');
        $cataloginventory_stock_item = $this
            ->resourceConnection
            ->getTableName('cataloginventory_stock_item');
        $json = file_get_contents('https://api-joch.brickx.software/api/apiservice.svc/stockactualoverview?apikey=57f56bdd-2578-4bcb-a992-6bad7e79f48f');

        // Decode the JSON string into an object
        $obj = json_decode($json);

        // In the case of this input, do key and array lookups to get the values
        //echo "<pre>";
        //var_dump($obj);
        //echo $obj.length;
        //  echo "<table><tr><td>Pname</td><td>PCode</td><td>Stock</td></tr>";
        $i = 1;
        foreach ($obj as $objs)
        {
            //  var_dump($objs);
            foreach ($objs as $data)
            {

                echo "Skipping -- " . '<br>';

                $select = $connection->select()
                    ->from($catalog_product_entity_varchar, 'entity_id')->where('value = :supplier_code');
                $supplier_code = $data->Code;
                $bind = [':supplier_code' => (string)$supplier_code];
                $pid = (int)$connection->fetchOne($select, $bind);
                if ($pid >= 1)
                {
                    //  echo $pid. '<br>';
                    // For Update query
                    $id = 1;
                    $query = "UPDATE " . $cataloginventory_stock_item . " SET qty = " . $data->CurrentStock . " WHERE product_id = $pid;";
                    $connection->query($query);
                    echo '<span style="color:red;">Updated product Id - </span>' . $pid . ' - Qty - ' . $data->CurrentStock . '<br>';
                }

            }
        }

    }

    function UpdateStockApi($supcode, $stockvalue)
    {

        if ($supcode)
        {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $prodColl = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
            $collection = $prodColl->addAttributeToSelect(['name', 'sku'])
                ->addAttributeToFilter('supplier_code', ['eq' => $supcode])->load();
            if ($collection->getData())
            {
                echo "s" . $supcode; // exit;
                $all_code = $collection->getData();
                //  echo $all_code[0]['entity_id'];
                $pid = $all_code[0]['entity_id'];
                $sku = $all_code[0]['sku'];

                $stockRegistry = $objectManager->create('Magento\CatalogInventory\Api\StockRegistryInterface');
                $stockValue = $stockvalue;
                //Need to load stock item
                $stockItem = $stockRegistry->getStockItem($pid);
                $stockItem->setData('qty', $stockValue); //set updated quantity
                /*    //$stockItem->setData('manage_stock',$stockData['manage_stock']);
                      //$stockItem->setData('is_in_stock',$stockData['is_in_stock']);
                      //$stockItem->setData('use_config_notify_stock_qty',1);
                */

                $stockRegistry->updateStockItemBySku($sku, $stockItem);
                echo "Product data has been updated. SKU - " . $sku;
            }

        }

    }

}

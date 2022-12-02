<?php

namespace Ennovations\Mymodule\Cron;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
//use Magento\Catalog\Model\ProductFactory ProductFactory;
use Magento\Framework\App\ResourceConnection;

class Run
{
    protected $resultPageFactory;
    //protected $productFactory;
    protected $resourceConnection;

    public function __construct(Context $context, PageFactory $resultPageFactory, ResourceConnection $resourceConnection) //ProductFactory $productFactory)

    {
      //  parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resourceConnection = $resourceConnection;
        //$this->productFactory = $productFactory;

    }

    public function execute()
    {

      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Api-Qty-Update.log');
      $logger = new \Zend\Log\Logger();
      $logger->addWriter($writer);

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


        $i = 1;
        foreach ($obj as $objs)
        {
            //  var_dump($objs);
            foreach ($objs as $data)
            {
              //  $logger->info("Skipping...". $data->Code);

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
                    $logger->info("Updated...". $data->Code . ' - Qty - ' . $data->CurrentStock . 'Product Id - ' . $pid) ;
                    //echo '<span style="color:red;">Updated product Id - </span>' . $pid . ' - Qty - ' . $data->CurrentStock . '<br>';
                }

            }
        }

    }


}

<?php
namespace BoostMyShop\AdvancedStock\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\ObjectManagerFactory;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Magento\Framework\Setup\SchemaSetupInterface;


class SyncWarehouseFromPo extends Command
{
    protected $_state;
    protected $_warehouseFactory;
    protected $_warehouseItemFactory;
    protected $_productCollectionFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    )
    {
        $this->_state = $state;
        $this->_warehouseFactory = $warehouseFactory;
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $options = [
            new InputOption(
                'warehouse_id',
                null,
                InputOption::VALUE_REQUIRED,
                'Warehouse ID (required)'
            )
        ];

        $this->setName('bms_advancedstock:sync_warehouse_from_po')->setDescription('synchronise warehouse from Po.')->setDefinition($options);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START sync warehouse');

        try{
            $this->_state->setAreaCode('adminhtml');
        }catch(\Exception $ex)
        {
        }

        $warehouseId = $input->getOption('warehouse_id');
        if (!$warehouseId)
            throw new \Exception('Warehouse id missing');
        $warehouse = $this->_warehouseFactory->create()->load($warehouseId);
        if (!$warehouse->getId())
            throw new \Exception('Warehouse #'.$warehouseId.' does not exist');
        if(!$warehouse->getw_sync_stock_from_po())
            throw new \Exception('Warehouse #'.$warehouseId.' Sync stock from PO option is not enabled');

        $output->writeln('start synchronise stock from PO');

        $productCollection = $this->getProducts();

        foreach ($productCollection as $product){
            $this->_warehouseItemFactory
                ->create()
                ->loadByProductWarehouse($product->getId(), $warehouse->getId())
                ->setwi_physical_quantity($product->getqty_to_receive())
                ->save();
        }

        $output->writeln('END synchronise stock');
    }
    protected function getProducts()
    {

        $collection = $this->_productCollectionFactory
                        ->create()
                        ->addFieldToFilter('qty_to_receive', array('gt' => 0));

        return $collection;
    }
}

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


class RefreshSalesHistory extends Command
{
    protected $_salesHistoryFactory;
    protected $_productCollectionFactory;
    protected $_resourceConnection;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\SalesHistoryFactory $salesHistoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->_salesHistoryFactory = $salesHistoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_resourceConnection = $resourceConnection;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('bms_advancedstock:refresh_sales_history')->setDescription('Refresh sales history for every products');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = time();
        $output->writeln('START refresh sales history');

        try{
            $this->_state->setAreaCode('adminhtml');
        }catch(\Exception $ex)
        {
        }

        $collection = $this->getProductIds();
        $count = count($collection);
        $output->writeln('Products to calculate history : '.$count);

        $processed = 0;
        $lastProgessPercent = null;

        foreach($collection as $productId)
        {
            $this->_salesHistoryFactory->create()->updateForProduct($productId);
            $progessPercent = (int)($processed / $count * 100);
            if ($progessPercent != $lastProgessPercent)
            {
                $output->writeln('Progress : '.$progessPercent.'%');
                $lastProgessPercent = $progessPercent;
            }
            $processed++;
        }

        $duration = time() - $start;
        $output->writeln('END refresh sales history in '.$duration.' seconds');
    }

    protected function getProductIds()
    {
        $connection = $this->_resourceConnection->getConnection();
        $select = $connection
            ->select()
            ->from($this->_resourceConnection->getTableName('sales_order_item'), array(new \Zend_Db_Expr('DISTINCT product_id')))
            ->where('product_type IN ("simple", "grouped")');
        return $connection->fetchCol($select);

    }

}

<?php

namespace BoostMyShop\Supplier\Console\Command;

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

class InitSupplyDiscontinued extends Command
{
    protected $_state;
    protected $_productAction;
    protected $_productCollectionFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    )
    {
        $this->_state = $state;
        $this->_productAction = $productAction;
        $this->_productCollectionFactory = $productCollectionFactory;

        parent::__construct();
    }

    protected function configure()
    {

        $this->setName('bms_supplier:init_supply_discontinued')->setDescription('Init discontinued attribute for all products');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START init supply discontinued');

        try
        {
            $this->_state->setAreaCode('adminhtml');
        }
        catch(\Exception $ex)
        {
            //nothing
        }

        $collection = $this->getProductIds();
        $arrays = array_chunk($collection, 200);
        $count = count($arrays);
        $i = 1;
        foreach($arrays as $array)
        {
            $this->_productAction->updateAttributes($array, ['supply_discontinued' => 0], 0);
            $output->writeln($i.' / '.$count.' batch of products processed');
            $i++;
        }

        $output->writeln('END init supply discontinued');
    }

    protected function getProductIds()
    {
        return $this->_productCollectionFactory
            ->create()
            ->getAllIds();
    }
}

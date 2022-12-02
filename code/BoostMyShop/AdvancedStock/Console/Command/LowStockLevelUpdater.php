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


class LowStockLevelUpdater extends Command
{
    protected $_lowStockLevelUpdater;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\LowStockLevelUpdater $lowStockLevelUpdater
    )
    {
        $this->_state = $state;
        $this->_lowStockLevelUpdater = $lowStockLevelUpdater;

        parent::__construct();
    }

    protected function configure()
    {

        $this->setName('bms_advancedstock:low_stock_level_updater')->setDescription('Update products warning and ideal stock level');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START low stock level updater');

        try{
            $this->_state->setAreaCode('adminhtml');
        }catch(\Exception $ex)
        {
        }

        $this->_lowStockLevelUpdater->run();

        $output->writeln('END low stock level updater');
    }


}

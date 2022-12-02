<?php
namespace BoostMyShop\OrderPreparation\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SomeCommand
 */
class PregenerateShippingLabels extends Command
{
    protected $_pregenerateLabel;
    protected $_state;

    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\OrderPreparation\Helper\PregenerateLabel $pregenerateLabel
    )
    {
        $this->_state = $state;
        $this->_pregenerateLabel = $pregenerateLabel;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('bms_orderpreparation:pregenerate_shipping_labels')->setDescription('Pre generate shipping label for orders in a batch');
        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try
        {
            $this->_state->setAreaCode('adminhtml');
        }
        catch(\Exception $ex)
        {
            //nothing
        }

        try{
            $this->_pregenerateLabel->process();
        }
        catch(\Exception $e)
        {
            $output->writeln($e->getMessage());
        }
    }
}

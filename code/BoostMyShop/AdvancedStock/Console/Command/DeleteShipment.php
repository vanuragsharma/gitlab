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

class DeleteShipment extends Command
{
    protected $_state;
    protected $_shipmentModel;

    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\Order\Shipment $shipmentModel
    )
    {
        $this->_state = $state;
        $this->_shipmentModel = $shipmentModel;

        parent::__construct();
    }

    protected function configure()
    {
        $options = [
            new InputOption(
                'shipment_id',
                null,
                InputOption::VALUE_REQUIRED,
                'Shipment ID (required)'
            ),
        ];

        $this->setName('bms_advancedstock:delete_shipment')->setDescription('Shipment deletion from its ID')->setDefinition($options);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START shipment deletion');

        try{
            $this->_state->setAreaCode('adminhtml');
        }catch(\Exception $ex)
        {
        }

        $shipmentId = $input->getOption('shipment_id');
        if (!$shipmentId)
            throw new \Exception('Shipment ID is mandatory');

        $this->_shipmentModel->delete($shipmentId, true);

        $output->writeln('END shipment deletion');
    }
}

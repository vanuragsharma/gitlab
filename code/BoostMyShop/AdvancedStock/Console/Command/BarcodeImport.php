<?php


namespace BoostMyShop\AdvancedStock\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BarcodeImport extends Command
{

    protected $csv;
    protected $_dir;
    protected $_filesystem;
    protected $_productFactory;
    protected $advanceStockConfig;
    protected $productAction;

    
    const FILE_PATH = "file_path";


    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\File\Csv $csv,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \BoostMyShop\AdvancedStock\Model\Config $advanceStockConfig,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Framework\Filesystem $_filesystem

    )
    {
        $this->_dir = $dir;
        $this->csv = $csv;
        $this->_filesystem = $_filesystem;
        $this->_productFactory = $productFactory;
        $this->advanceStockConfig = $advanceStockConfig;
        $this->productAction = $productAction;
        parent::__construct();
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $file = $input->getArgument(self::FILE_PATH); 
        $file =    $this->_dir->getRoot().'/'.$file;
       
        if (!file_exists($file)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid CSV file, Please upload valid file.'));
        }
        
        $csvData = $this->csv->getData($file);
        $header = $csvData[0];

        if((!isset($header[0]) && !isset($header[1])))
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid formated CSV file'));

        if(isset($header[0]) && $header[0] != "sku")
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid CSV file, Column "sku" missing'));

        if(isset($header[1]) && $header[1] != "barcode")
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid CSV file, Column "barcode" missing'));

        $barcodeAttr = $this->advanceStockConfig->getBarcodeAttribute();
        if (!$barcodeAttr) {
            throw new \Magento\Framework\Exception\LocalizedException(__('barcode attribute not selected.'));
        }

        unset($csvData[0]);
        
        foreach ($csvData as $key => $proData) 
        {  
            try{ 
                $productId = $this->_productFactory->create()->getIdBySku($proData[0]);
                if (!$productId) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('sku does not exist.'));                   
                }
            }
            catch(\Exception $e)
            {
                $output->writeln($proData[0]." does not exist");
                continue;
            }
            
            try{
                $this->productAction->updateAttributes([$productId], [$barcodeAttr => $proData[1]], 0);
                $output->writeln("Barcode updated for " . $proData[0]);

            }
            catch(\Exception $e)
            {
                $output->writeln($proData[0]);
            }
        }         
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("boostmyshop_advancedstock:import_barcode");
        $this->addArgument('file_path', InputArgument::REQUIRED, __('Please Enter file Path'));
        $this->setDescription("");     
        parent::configure();
    }
}

<?php
namespace Yalla\Sales\Controller\Adminhtml\Index;
use Magento\Framework\App\Filesystem\DirectoryList;

class Export extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    protected $_resource;
   protected $_fileFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Magento\Framework\Filesystem $filesystem
, 
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
\Magento\Framework\View\Result\PageFactory $resultPageFactory,
    \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->_fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->resultPageFactory = $resultPageFactory;
        $this->_resource = $resource;
    }
    
    /**
     * Load the page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {
    	$isPost = $this->getRequest()->getPost();
        

        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('inventory_reservation');
        
        $sql = "select sku, quantity, (select SUM(quantity) from ".$tableName." as ir where ir.sku = main_table.sku) as salable_qty from inventory_stock_1 as main_table";


        
        $list = $connection->fetchAll($sql); //fetchRow($sql), fetchOne($sql),...

        $name = date('m_d_Y_H_i_s');
        $filepath = 'export/inventory_reservation' . $name . '.csv';
        $this->directory->create('export');
        /* Open file */
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $headers = ['sku','Saleable QTY'];
        /* Write Header */
        $stream->writeCsv($headers);
 
        
 
        foreach ($list as $key => $fields) {
            if(empty($fields['salable_qty']) || $fields['salable_qty'] == null){
                $sumofquantity = $fields['quantity'];
            }else{
				$sumofquantity = $fields['quantity'] + $fields['salable_qty'];
            }
            $newfile[$key]['sku'] = $fields['sku'];
			$newfile[$key]['quantity'] = $sumofquantity;
			
        }

        foreach($newfile as $file){
			$stream->writeCsv($file);
        }
 
        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder
 
        $csvfilename = 'Saleable_'.$name.'.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
        
    }
}

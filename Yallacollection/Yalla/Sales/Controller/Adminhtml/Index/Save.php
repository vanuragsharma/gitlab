<?php
namespace Yalla\Sales\Controller\Adminhtml\Index;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    protected $_resource;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory,
    \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context);
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
        
        $sku = $isPost['sku'];
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('inventory_reservation');
        
        $sql = "Delete FROM " . $tableName." Where sku = '".$sku."'";
		$connection->query($sql);
        
        $this->messageManager->addSuccess(__('Stock has been updated successfully.'));
        $this->_redirect('*/*/');
        
    }
}

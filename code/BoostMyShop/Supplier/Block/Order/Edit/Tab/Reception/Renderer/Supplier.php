<?php 

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Reception\Renderer;

use Magento\Framework\DataObject;

class Supplier extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_orderFactory;
    protected $_supplierFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\Supplier\Model\OrderFactory $orderFactory,
        \BoostMyShop\Supplier\Model\SupplierFactory $supplierFactory,
        array $data = []
    )
    {
        $this->_orderFactory = $orderFactory;
        $this->_supplierFactory = $supplierFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function render(DataObject $row)
    {
        $supplierName = $this->_orderFactory->create()->load($row->getpor_po_id())->getSupplier()->getsup_name();
       
        return $supplierName;
    }
}

<?php 

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Reception\Renderer;

use Magento\Framework\DataObject;

class PoReference extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_orderFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\Supplier\Model\OrderFactory $orderFactory,
        array $data = []
    )
    {
        $this->_orderFactory = $orderFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function render(DataObject $row)
    {
        $poReference = $this->_orderFactory->create()->load($row->getpor_po_id())->getpo_reference();
        $url = $this->getUrl('supplier/order/edit', ['po_id' => $row->getpor_po_id()]);
        $poReference = '<a href="'.$url.'">'.$poReference.'</a>';

        return $poReference;
    }
}

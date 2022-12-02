<?php 

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Reception\Filter;

class PoReferenceFilter extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    protected $receptionCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Reception\CollectionFactory $receptionCollectionFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        array $data = [])
    {
        $this->receptionCollectionFactory = $receptionCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * @return array|null
     */
    public function getCondition()
    {
        if (!$this->getValue()) {
            return null;
        }
        $PoIds = $this->orderCollectionFactory->create()->addFieldToFilter('po_reference', array("like" => "%".$this->getValue()."%"))->getAllIds();

        $reids = $this->receptionCollectionFactory->create()->addfieldtofilter('por_po_id', array('in' => $PoIds))->getAllIds();
        return ['in' => $reids];
    }
}

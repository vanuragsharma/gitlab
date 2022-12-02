<?php
namespace BoostMyShop\Organizer\Block\Preparation\Batch\Tab;

class Organizer extends \BoostMyShop\Organizer\Block\OrganizerTab
{
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $coreRegistry;
    }

    public function getCurrentObject()
    {
        return $this->_coreRegistry->registry('current_batch');
    }

    public function getCurrentObjectType()
    {
        return \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_BATCH;
    }

    public function getGridHtml()
    {
        $block = $this->getLayout()
            ->createBlock('\BoostMyShop\Organizer\Block\Organizer\Grid')
            ->setOrganizerContext($this->getCurrentObjectType(), $this->getCurrentObject()->getbob_id())
            ->setTemplate('Organizer/Grid.phtml')->toHtml();

        return $block;
    }
}
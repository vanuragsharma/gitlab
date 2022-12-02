<?php
namespace BoostMyShop\OrderPreparation\Block\Preparation;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected $_coreRegistry;
    protected $_config;

    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \BoostMyShop\OrderPreparation\Model\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->_config = $config;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('tab_container');
        $this->setTitle(__('Order Preparation'));

    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        if ($this->_config->getManyOrdersMode())
        {
            //show tabs with ajax and dont show numbers for partial / backorder / on hold tabs
            $block =  $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Preparation\Tab\InStock');
            $block->setManyOrdersMode(true);
            $html = $block->toHtml();
            $this->addTab(
                'tab_instock',
                [
                    'label' => __('In Stock')." (".$block->getCollection()->getSize().")",
                    'title' => __('In Stock')." (".$block->getCollection()->getSize().")",
                    'content' => $html
                ]
            );

            $this->addTab(
                'tab_partial',
                [
                    'label' => __('Partial'),
                    'title' => __('Partial'),
                    'class' => 'ajax',
                    'url'   => $this->getUrl('*/*/partialAjaxGrid', ['_current' => true, 'grid' => 'partial']),
                    'content' => '<div><p></p>Loading ...<p></p>Loading ...<p></p>Loading ...</div>'
                ]
            );

            $this->addTab(
                'tab_backorder',
                [
                    'label' => __('Backorder'),
                    'title' => __('Backorder'),
                    'class' => 'ajax',
                    'url'   => $this->getUrl('*/*/backorderAjaxGrid', ['_current' => true, 'grid' => 'backorder'])
                ]
            );

            $this->addTab(
                'tab_holded',
                [
                    'label' => __('On Hold'),
                    'title' => __('On Hold'),
                    'class' => 'ajax',
                    'url'   => $this->getUrl('*/*/holdedAjaxGrid', ['_current' => true, 'grid' => 'holded'])
                ]
            );
        }
        else
        {
            //show tabs pre-loaded with numbers in tabs
            $block =  $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Preparation\Tab\InStock');
            $block->setManyOrdersMode(false);
            $html = $block->toHtml();
            $this->addTab(
                'tab_instock',
                [
                    'label' => __('In Stock')." (".$block->getCollection()->getSize().")",
                    'title' => __('In Stock')." (".$block->getCollection()->getSize().")",
                    'content' => $html
                ]
            );

            $block =  $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Preparation\Tab\Partial');
            $block->setManyOrdersMode(false);
            $html = $block->toHtml();
            $this->addTab(
                'tab_partial',
                [
                    'label' => __('Partial')." (".$block->getCollection()->getSize().")",
                    'title' => __('Partial')." (".$block->getCollection()->getSize().")",
                    'content' => $html
                ]
            );

            $block =  $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Preparation\Tab\BackOrder');
            $block->setManyOrdersMode(false);
            $html = $block->toHtml();
            $this->addTab(
                'tab_backorder',
                [
                    'label' => __('Backorder')." (".$block->getCollection()->getSize().")",
                    'title' => __('Backorder')." (".$block->getCollection()->getSize().")",
                    'content' => $html
                ]
            );

            $block =  $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Preparation\Tab\Holded');
            $block->setManyOrdersMode(false);
            $html = $block->toHtml();
            $this->addTab(
                'tab_holded',
                [
                    'label' => __('On Hold')." (".$block->getCollection()->getSize().")",
                    'title' => __('On Hold')." (".$block->getCollection()->getSize().")",
                    'content' => $html
                ]
            );

        }
        $this->_eventManager->dispatch('bms_orderpreparation_preparation_tabs', ['tabs' => $this, 'layout' => $this->getLayout()]);
        $isBatchEnable = $this->_config->isBatchEnable();
        if($isBatchEnable) {
            $block = $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Preparation\Batch');
            $html = $block->toHtml();
            $orderCount = 0;
            foreach($block->getCollection()->getItems() as $batch)
                $orderCount += $batch->getbob_order_count();
            $this->addTab(
                'tab_batches_new',
                [
                    'label' => __('New batches')." (".$block->getCollection()->getSize()." - ".$orderCount.")",
                    'title' => __('New batches')." (".$block->getCollection()->getSize()." - ".$orderCount.")",
                    'content' => $html,
                    'active' => true
                ]
            );
            $block = $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Preparation\BatchPrinted');
            $html = $block->toHtml();
            $orderCount = 0;
            foreach($block->getCollection()->getItems() as $batch)
            {
                $orderCount += $batch->getProcessingOrderCount();
            }
            $this->addTab(
                'tab_batches_printed',
                [
                    'label' => __('Active batches')." (".$block->getCollection()->getSize()." - ".$orderCount.")",
                    'title' => __('Active batches')." (".$block->getCollection()->getSize()." - ".$orderCount.")",
                    'content' => $html,
                    'active' => true
                ]
            );
            $block = $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Batch\Grid');
            $html = $block->toHtml();
            $this->addTab(
                'tab_batches_history',
                [
                    'label' => __('Batches history') . " (" . $block->getCollection()->getSize() . ")",
                    'title' => __('Batches history') . " (" . $block->getCollection()->getSize() . ")",
                    'content' => $html,
                    'active' => true
                ]
            );
            $this->setActiveTab('tab_batches_new');
        }
        else
        {
            $block =  $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Preparation\InProgress');
            $html = $block->toHtml();
            $this->addTab(
                'tab_progress',
                [
                    'label' => __('In progress')." (".$block->getCollection()->getSize().")",
                    'title' => __('In progress')." (".$block->getCollection()->getSize().")",
                    'content' => $html,
                    'active' => true
                ]
            );
        }

        return parent::_beforeToHtml();
    }
}
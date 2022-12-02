<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected $_coreRegistry;

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Information'));
    }

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;

        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    protected function getCarrierTemplate()
    {
        return $this->_coreRegistry->registry('current_carrier_template');
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\Main')->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'shipping_cost_matrix',
            [
                'label' => __('Shipping cost matrix'),
                'title' => __('Shipping cost matrix'),
                'content' => $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\ShippingCostMatrix')->toHtml()
            ]
        );

        if ($this->getCarrierTemplate()->getId())
        {
            $this->addTab(
                'export_section',
                [
                    'label' => __('Export'),
                    'title' => __('Export'),
                    'content' => $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\Export')->toHtml()
                ]
            );

            if ($this->getCarrierTemplate()->getct_type() == \BoostMyShop\OrderPreparation\Model\CarrierTemplate::kTypeOrderDetailsExport)
            {
                $this->addTab(
                    'import_section',
                    [
                        'label' => __('Import'),
                        'title' => __('Import'),
                        'content' => $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\Import')->toHtml()
                    ]
                );

                $this->addTab(
                    'helper_section',
                    [
                        'label' => __('Available codes'),
                        'title' => __('Available codes'),
                        'content' => $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\Helper')->toHtml()
                    ]
                );

            }

            if ($this->getCarrierTemplate()->getct_type() == \BoostMyShop\OrderPreparation\Model\CarrierTemplate::kTypeSimpleAddressLabel)
            {
                $this->addTab(
                    'label_configuration',
                    [
                        'label' => __('Label configuration'),
                        'title' => __('Label configuration'),
                        'content' => $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab\Label')->toHtml()
                    ]
                );
            }
            $renderer = $this->getCarrierTemplate()->getRenderer();
            if ($renderer)
                $renderer->addCustomTabToCarrierTemplate($this->getCarrierTemplate(), $this, $this->getLayout());

        }

        return parent::_beforeToHtml();
    }
}

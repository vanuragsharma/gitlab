<?php
namespace BoostMyShop\OrderPreparation\Block\Packing\ChangeShippingMethodPopup;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected $_coreRegistry;
    protected $_config = null;
    protected $_carrierTemplateHelper = null;

    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \BoostMyShop\OrderPreparation\Model\Config $config,
        \BoostMyShop\OrderPreparation\Helper\CarrierTemplate $carrierTemplateHelper,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);

        $this->_config = $config;
        $this->_carrierTemplateHelper = $carrierTemplateHelper;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('change_shipping_method_tabs');
        $this->setDestElementId('change_shipping_methods_page_tabs');
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {

        $block =  $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Packing\ChangeShippingMethodPopup\AllMethods');
        $html = $block->toHtml();

        $this->addTab(
            'tab_allmethods',
            [
                'label' => __('All methods'),
                'title' => __('All methods'),
                'content' => $html
            ]
        );

        if ($this->_carrierTemplateHelper->hasTemplateWithRates())
        {
            $block =  $this->getLayout()->createBlock('BoostMyShop\OrderPreparation\Block\Packing\ChangeShippingMethodPopup\Calculator');
            $html = $block->toHtml();

            $this->addTab(
                'tab_calculator',
                [
                    'label' => __('Calculator'),
                    'title' => __('Calculator'),
                    'content' => $html
                ]
            );
        }

        return parent::_beforeToHtml();
    }
}
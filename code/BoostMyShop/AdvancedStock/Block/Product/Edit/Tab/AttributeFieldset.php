<?php

namespace BoostMyShop\AdvancedStock\Block\Product\Edit\Tab;

class AttributeFieldset extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    protected $_blockFactory;

    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        array $data = [])
    {
        $this->_blockFactory = $blockFactory;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    public function getElementHtml()
    {
        return  $this->_blockFactory->createBlock('BoostMyShop\AdvancedStock\Block\Product\Edit\Tab\Renderer\AttributeFieldsetBlock')
            ->setTemplate('BoostMyShop_AdvancedStock::ErpProduct/Edit/Tab/AttributeFieldset.phtml')
            ->toHtml();
    }

}

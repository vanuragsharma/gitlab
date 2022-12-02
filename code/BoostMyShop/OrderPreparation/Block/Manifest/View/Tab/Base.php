<?php
namespace BoostMyShop\OrderPreparation\Block\Manifest\View\Tab;

class Base extends \Magento\Backend\Block\Widget\Container
{
    protected $_manifest;
    protected $_registry;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    public function getManifest()
    {
        if(!$this->_manifest)
            $this->_manifest = $this->_registry->registry('current_manifest');

        return $this->_manifest;
    }

    public function cleanReference($reference)
    {
        $t = explode('_', $reference);
        if (isset($t[0]) && count($t) > 1)
            unset($t[0]);
        return implode('_', $t);
    }
}
<?php
namespace BoostMyShop\OrderPreparation\Block\Batch\Edit\Tab;

class Base extends \Magento\Backend\Block\Widget\Container
{
    protected $_batch;
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

    public function getBatch()
    {
        if(!$this->_batch)
            $this->_batch = $this->_registry->registry('current_batch');

        return $this->_batch;
    }

    public function cleanReference($reference)
    {
        $t = explode('_', $reference);
        if (isset($t[0]) && count($t) > 1)
            unset($t[0]);
        return implode('_', $t);
    }
}
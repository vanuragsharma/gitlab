<?php
/**
 * @category   Mageants EventManager
 * @package    Mageants_EventManager
 * @copyright  Copyright (c) 2019 Mageants
 * @author     Mageants Team <support@Mageants.com>
 */
namespace Mageants\EventManager\Block\Index;

class Link extends \Magento\Framework\View\Element\Template
{
   /**
    * @var \Magento\Store\Model\StoreManagerInterface $storeManager
    */
    public $storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     */

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * @return currentstore 
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }
}

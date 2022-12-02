<?php

namespace BoostMyShop\Erp\Helper;

class Boostmyshop
{
    protected $_moduleManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->_authorization = $context->getAuthorization();
        $this->_moduleManager = $moduleManager;
    }


    public function advancedStockModuleIsInstalled()
    {
        return $this->_moduleManager->isEnabled('BoostMyShop_AdvancedStock');
    }

    public function isAllowedResource($resource)
    {
        return $this->_authorization->isAllowed($resource);
    }

}
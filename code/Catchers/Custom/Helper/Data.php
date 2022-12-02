<?php

namespace Catchers\Custom\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    protected $orderFactory;
    protected $shipmentFactory;
    protected $orderModel;
    protected $trackFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function customerGroupsConfig()
    {
        $result = [];
        $customer_groups = $this->scopeConfig->getValue(
            'customer_group/general/group_emails',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        foreach (json_decode($customer_groups) as $key => $group) {
            $result[$group->cusgroup] = $group->cusgroupemail;
        }
        return $result;
    }
}

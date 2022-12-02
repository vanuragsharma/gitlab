<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ShowPriceAfterLogin
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ShowPriceAfterLogin\Model\Config\Source;

class CustomerGroupOptions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Webkul\ShowPriceAfterLogin\Helper\Data
     */
    protected $helper;

    /**
     * __construct function
     *
     * @param \Webkul\ShowPriceAfterLogin\Helper\Data $helper
     */
    public function __construct(
        \Webkul\ShowPriceAfterLogin\Helper\Data $helper
    ) {
    
        $this->helper = $helper;
    }
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getGroupsLists();
    }

    /**
     * Type of customer groups
     * @return array
     */
    public function getGroupsLists()
    {
        $groupOptions = $this->helper->getGroupsLists();
        return $groupOptions;
    }
}

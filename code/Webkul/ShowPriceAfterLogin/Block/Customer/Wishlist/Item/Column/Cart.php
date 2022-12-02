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
namespace Webkul\ShowPriceAfterLogin\Block\Customer\Wishlist\Item\Column;

use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\View\ConfigInterface;

class Cart extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Cart
{
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = [],
        ?ConfigInterface $config = null,
        ?UrlBuilder $urlBuilder = null,
        \Webkul\ShowPriceAfterLogin\Helper\Data $helper,
        ?View $productView = null
    ) {
        $this->helper = $helper;
        parent::__construct($context, $httpContext, $data, $config, $urlBuilder, $productView);
    }
    public function getHelper()
    {
        return $this->helper;
    }
}

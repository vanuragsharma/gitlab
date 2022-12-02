<?php

namespace Magecomp\Emailquotepro\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;
class TotalActions extends Column
{
    /** Url path */
    /** @var UrlInterface */
    protected $urlBuilder;
    protected $_storeManager;
    protected $_currencyFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        CurrencyFactory $currencyFactory,
        array $components = [],
        array $data = []

    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->_storeManager = $storeManager;
        $this->_currencyFactory = $currencyFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource( array $dataSource )
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item)
            {
                $currency = $this->_currencyFactory->create()->load($this->_storeManager->getStore($item['storeview'])->getCurrentCurrencyCode());
                $currencySymbol = $currency->getCurrencySymbol();

                $formattedPrice = $currencySymbol.$item[$fieldName];

                $item[$fieldName] = $formattedPrice;
            }
        }
        return $dataSource;
    }
}

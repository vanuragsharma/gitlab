<?php

namespace Yalla\Apis\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Currency information acquirer class
 */
class Countries implements \Yalla\Apis\Api\CountriesInterface
{
    /**
     * @var \Magento\Directory\Model\Data\CountryInformationFactory
     */
    protected $countryInformationFactory;

    /**
     * @var \Magento\Directory\Model\Data\RegionInformationFactory
     */
    protected $regionInformationFactory;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

	protected $request;

    /**
     * @param \Magento\Directory\Model\Data\CountryInformationFactory $countryInformationFactory
     * @param \Magento\Directory\Model\Data\RegionInformationFactory $regionInformationFactory
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Directory\Model\Data\CountryInformationFactory $countryInformationFactory,
        \Magento\Directory\Model\Data\RegionInformationFactory $regionInformationFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
	\Magento\Framework\App\Request\Http $request
    ) {
        $this->countryInformationFactory = $countryInformationFactory;
        $this->regionInformationFactory = $regionInformationFactory;
        $this->directoryHelper = $directoryHelper;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
	$this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountries()
    {
        $countriesInfo = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
	    //Multi store view
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
		$apiHelper = $objectManager->get('Yalla\Apis\Helper\Data');
        $apiHelper->setStore($lang, $country);
        //Multi store view

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        $storeLocale = $this->scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $store->getCode()
        );

$resolver = $objectManager->get('Magento\Framework\Locale\Resolver');

        $countries = $this->directoryHelper->getCountryCollection($store);
        $regions = $this->directoryHelper->getRegionData();
        
        foreach ($countries as $data) {
                $countryInfo = $this->setCountryInfo($data, $regions, $storeLocale);
                $countriesInfo[$countryInfo['country_name']] = $countryInfo;
        }
        ksort($countriesInfo);
		$countriesInfo = array_values($countriesInfo);
        echo json_encode(['success' => 'true', 'msg' => '', 'collection' => $countriesInfo]);
		exit;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryInfo($countryId)
    {
        $store = $this->storeManager->getStore();
        $storeLocale = $this->scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $store->getCode()
        );

        $countriesCollection = $this->directoryHelper->getCountryCollection($store)->load();
        $regions = $this->directoryHelper->getRegionData();
        $country = $countriesCollection->getItemById($countryId);

        if (!$country) {
            throw new NoSuchEntityException(
                __(
                    'Requested country is not available.'
                )
            );
        }
        $countryInfo = $this->setCountryInfo($country, $regions, $storeLocale);

        return $countryInfo;
    }

    /**
     * Creates and initializes the information for \Magento\Directory\Model\Data\CountryInformation
     *
     * @param \Magento\Directory\Model\ResourceModel\Country $country
     * @param array $regions
     * @param string $storeLocale
     * @return \Magento\Directory\Model\Data\CountryInformation
     */
    protected function setCountryInfo($country, $regions, $storeLocale)
    {
        $countryId = $country->getCountryId();
        
        $countryInfo['id'] = $countryId;
        $countryInfo['iso2_code'] = $country->getData('iso2_code');
        $countryInfo['iso2_code'] = $country->getData('iso3_code');
        if($country->getData('iso3_code') == 'ANT'){
        	$countryInfo['country_name'] = 'Netherlands Antilles';
        }else{
        	$countryInfo['country_name'] = $country->getName($storeLocale);
        }
        $countryInfo['country_name_eng'] = $country->getName('en_US');

        if (array_key_exists($countryId, $regions)) {
            $regionsInfo = [];
            foreach ($regions[$countryId] as $id => $regionData) {
                $regionsInfo[] = [
			'id' => $id,
			'code' => $regionData['code'],
		        'name' => $regionData['name']
		];
            }
            $countryInfo['regions'] = $regionsInfo;
        }

        return $countryInfo;
    }
}

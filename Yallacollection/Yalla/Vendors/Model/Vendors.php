<?php

namespace Yalla\Vendors\Model;

class Vendors extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface {

    const CACHE_TAG = 'vendors_vendors';

    protected $_cacheTag = 'vendors_vendors';
    protected $_eventPrefix = 'vendors_vendors';

    protected function _construct()
    {
        $this->_init('Yalla\Vendors\Model\ResourceModel\Vendors');
    }
    
    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues() {
        $values = [];

        return $values;
    }

    protected function getHelper() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->create('Yalla\Vendors\Helper\Data');
    }

/**
     * Return Vendors List
     *
     * @return array
     */
    public function getList($display_type = '') {
        $collection = $this->getCollection()
                ->addFieldToFilter('banner_status', 1);

	if ($display_type) {
            $collection->addFieldToFilter('banner_display_type', $display_type);
        }

	$collection->getSelect()->orderRand();

        $vendors = array();
        try {
            foreach ($collection as $banner) {

                // Banner file name should not empty
                if (empty($banner->getBannerFileName())) {
                    continue;
                }

                // Check banner does not exist
                $banner_exist = $this->getHelper()->CheckBannerExist($banner->getBannerFileName());

                if (!$banner_exist) {
                    continue;
                }

                $target = $banner->getBannerTargetId();

                // Change target value in case of E
                if ($banner->getBannerTargetType() == 'E') {
                    $target = $banner->getBannerExUrl();
                }

                // Get banner url using helper
                $banner_url = $this->getHelper()->getBannerUrl($banner->getBannerFileName());

                $vendors[] = array(
                    'id' => $banner->getBannerId(),
                    'name' => $banner->getBannerAlt(), 
                    'target' => $banner->getBannerTargetType(),
                    'target_id' => $target,
                    'image_url' => $banner_url
                );
            }

		return $vendors;
        } catch (\Exception $e) {
            return [];
        }
        
        return [];
    }

}

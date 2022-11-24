<?php

namespace Yalla\Apis\Model\Data;

use Yalla\Apis\Api\Data\ConfigurableInterface;

/**
 * Class Customer
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Configurable extends \Magento\Framework\Api\AbstractExtensibleObject implements
ConfigurableInterface {

    /**
     * Get option ids
     *
     * @return string|null
     */
    public function getOptionId(){
        return $this->_get(self::OPTION_ID);
    }

    /**
     * Set option ids
     *
     * @param string $option_id
     * @return $this
     */
    public function setOptionId($option_id){
        return $this->setData(self::OPTION_ID, $option_id);
    }

    /**
     * Get attribute ids
     *
     * @return string|null
     */
    public function getAttributeId(){
        return $this->_get(self::ATTRIBUTE_ID);
    }

    /**
     * Set attribute ids
     *
     * @param string $attribute_id
     * @return $this
     */
    public function setAttributeId($attribute_id){
        return $this->setData(self::ATTRIBUTE_ID, $attribute_id);
    }

    /**
     * Get Configurable product id
     *
     * @return string|null
     */
    public function getParentProductId(){
        return $this->_get(self::PARENT_PRODUCT_ID);
    }

    /**
     * Set Configurable product id
     *
     * @param string $parent_product_id
     * @return $this
     */
    public function setParentProductId($parent_product_id){
        return $this->setData(self::PARENT_PRODUCT_ID, $parent_product_id);
    }
}

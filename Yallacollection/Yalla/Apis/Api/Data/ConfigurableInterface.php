<?php

namespace Yalla\Apis\Api\Data;

/**
 * Cart Parameters interface.
 * @api
 */
interface ConfigurableInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const OPTION_ID = 'optionId';
    const ATTRIBUTE_ID = 'attributeId';
    const PARENT_PRODUCT_ID = 'parentProductId';
    
    /**#@-*/

    /**
     * Get option ids
     *
     * @return string|null
     */
    public function getOptionId();

    /**
     * Set option ids
     *
     * @param string $option_id
     * @return $this
     */
    public function setOptionId($option_id);

    /**
     * Get attribute ids
     *
     * @return string|null
     */
    public function getAttributeId();

    /**
     * Set attribute ids
     *
     * @param string $attribute_id
     * @return $this
     */
    public function setAttributeId($attribute_id);

    /**
     * Get Configurable product id
     *
     * @return string|null
     */
    public function getParentProductId();

    /**
     * Set Configurable product id
     *
     * @param string $parent_product_id
     * @return $this
     */
    public function setParentProductId($parent_product_id);

}

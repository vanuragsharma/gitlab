<?php

namespace Yalla\Apis\Api;

/**
 * @api
 * @since 100.0.2
 */
interface ConfigurableInterface
{
    
    /**
     *
     * @param ConfigurableInterface $options
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customerId is not found
     * @return array
     */
    public function configurableOptions();
    
}

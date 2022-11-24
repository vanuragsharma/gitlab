<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Yalla\Apis\Api\Customer\Api;

use Yalla\Apis\Api\Data\CustomerInterface;

/**
 * Interface CustomerNameGenerationInterface
 *
 * @api
 * @since 100.1.0
 */
interface CustomerNameGenerationInterface
{
    /**
     * Concatenate all customer name parts into full customer name.
     *
     * @param CustomerInterface $customerData
     * @return string
     * @since 100.1.0
     */
    public function getCustomerName(CustomerInterface $customerData);
}

<?php

namespace Yalla\Apis\Api;

interface AddressManagementInterface
{

    /**
     * Create new customer address.
     * @return array
     */

    public function createAddress();

    /**
     * Retrieve customer address.
     * @param string $customer_id
     * @return array
     */
    public function retrieveAddress($customer_id);

    /**
     * Update Address details.
     * @return array
     */
    public function updateAddress();

    /**
     * Delete customer address.
     * @param string $address_id
     * @return array
     */
    public function deleteAddress($address_id);
    
}

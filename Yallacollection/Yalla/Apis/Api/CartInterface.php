<?php

namespace Yalla\Apis\Api;


interface CartInterface
{
    /**
     * Returns customer Cart
     *
     * @param int $customerId
     * @return array
     */
    public function getCartData($customerId);

    /**
     * Update customer Cart item
     *
     * @return array
     */
    public function updateCartItem();

    /**
     * Remove item from customer Cart
     *
     * @return array
     */
    public function deleteCartItem();
}

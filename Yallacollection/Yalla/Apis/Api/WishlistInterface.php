<?php

namespace Yalla\Apis\Api;

/**
 * Interface WishlistInterface
 * @api
 */
interface WishlistInterface
{
    /**
     * Returns customer's wishlist
     *
     * @param int $customerId
     * @return array
     */
    public function get($customerId);
    
    /**
     * Returns customer's wishlist
     *
     * @return array
     */
    public function add();
    
    /**
     * Returns customer's wishlist
     *
     * @return array
     */
    public function delete();

}

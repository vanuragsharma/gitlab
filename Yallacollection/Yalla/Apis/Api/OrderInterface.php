<?php

namespace Yalla\Apis\Api;

/**
 * Interface OrderInterface
 * @api
 */
interface OrderInterface
{
    /**
     * Returns customer's orders
     *
     * @return array
     */
    public function getOrders();

}

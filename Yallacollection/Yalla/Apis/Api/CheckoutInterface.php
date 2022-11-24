<?php

namespace Yalla\Apis\Api;


interface CheckoutInterface
{
    /**
     * Place order
     *
     * @return array
     */
    public function submitOrder();
    
    /**
     * Update order and payment status
     *
     * @return array
     */
    public function updateStatus();

}

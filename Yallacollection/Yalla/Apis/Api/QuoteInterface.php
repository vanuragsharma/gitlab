<?php

namespace Yalla\Apis\Api;


interface QuoteInterface
{
    /**
     * Return Quote
     *
     * @return array
     */
    public function quoteReview();
    
    /**
     * Apply Coupon
     *
     * @return array
     */
    public function redeemCoupon();
    
    /**
     * Redeem points
	 *
     * @return array
     */
    public function redeemPoints();
    
    /**
     * Reorder
	 *
     * @return array
     */
    public function reorder();

    /**
     * Apply donation
	 *
     * @return array
     */
    public function applyDonation();
}

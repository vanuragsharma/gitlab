<?php

namespace Yalla\Apis\Api;

interface CustomerApiInterface {

    /**
     * Return Added Customer.
     * @return array
     *
     */
    public function socialregister();

    /**
     * Return Updated Customer.
     * @return array
     *
     */
    public function editProfile();

    /**
     * Returns customer profile data
     * 
     * @return array
     */
    public function getProfile();
}

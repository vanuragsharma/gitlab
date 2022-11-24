<?php

namespace Yalla\Apis\Api;

interface HomeInterface {

    /**
     * Return Home Data.
     * @param int $customerId
     * @return json
     *
     */
    public function getData($customerId);
    
    /**
     * Checkversion of app
     * @return json
     */
    public function checkVersion();

}

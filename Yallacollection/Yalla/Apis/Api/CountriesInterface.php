<?php

namespace Yalla\Apis\Api;

/**
 * Country information acquirer interface
 *
 * @api
 * @since 100.0.2
 */
interface CountriesInterface
{
    /**
     * Get all countries and regions information for the store.
     *
     * @return string
     */
    public function getCountries();

}

<?php

namespace Yalla\Apis\Api;

/**
 * Interface BrandsInterface
 * @api
 */
interface BrandsInterface
{
    /**
     * Returns all brands
     *
     * @return array
     */
    public function getAll();

    /**
     * Returns all characters
     *
     * @return array
     */
    public function getAllCharacters();
}

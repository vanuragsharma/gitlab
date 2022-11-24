<?php

namespace Yalla\Apis\Api;

interface ProductApiInterface {

    /**
     * Retrieve list of Products by category
     *
     * @param int $categoryId
     * @param int $page
     * @param int $pageSize
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return array
     */
    public function getListByCategory($categoryId = null, $page = null, $pageSize = null);

}

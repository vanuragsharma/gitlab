<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Yalla\Apis\Api;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;

/**
 * @api
 * @since 100.0.2
 */
interface ProductRepositoryInterface
{

    /**
     * Get info about product by product SKU
     *
     * @param string $sku
     * @param bool $editMode
     * @param int|null $storeId
     * @param bool $forceReload
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($sku, $editMode = false, $storeId = null, $forceReload = false);

    /**
     * Get info about product by product id
     *
     * @param int $productId
     * @param bool $editMode
     * @param int|null $storeId
     * @param bool $forceReload
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($productId, $editMode = false, $storeId = null, $forceReload = false);

    /**
     * Get info about product by product id
     *
     * @param int $productId
     * @param int $customerId
     * @param int|null $storeId
     * @param bool $forceReload
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function productDetails($productId, $customerId = 0, $storeId = null, $forceReload = false);

    /**
     * Get product list
     *
     * @return array
     */
    public function getList();

    /**
     * Save review
     * @param int $productId
     * @return array
     */
    public function submitReview($productId);
}

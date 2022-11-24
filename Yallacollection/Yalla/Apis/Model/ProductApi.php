<?php

namespace Yalla\Apis\Model;

use Yalla\Apis\Api\ProductApiInterface;

class ProductApi implements ProductApiInterface {

    protected $_productsFactory;
    protected $_collection;
    protected $_resource;
    protected $categoryRepository;

    public function __construct(
            \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $productsFactory, array $data = []
    ) {
        $this->_productsFactory = $productsFactory;
    }

    /**
     * Retrieve list of Products by category
     *
     * @param int $categoryId
     * @param int $page
     * @param int $pageSize
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return array
     */
    public function getListByCategory($categoryId = null, $page = null, $pageSize = null) {
        $category = null;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($categoryId !== null) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
            if (!$category) {
                return array(array("product_count" => 0, "data" => []));
            }
        }

        if (!$page)
            $page = 1;
        if (!$pageSize)
            $pageSize = 10;

        $collectionTotal = $this->_productsFactory->create()->addAttributeToSelect('*');
        if ($categoryId !== null) {
            $collectionTotal->addCategoryFilter($category);
        }
        $total = $collectionTotal->count();

        $list = array();
        if ($total) {
            $collectionTotal = $collectionTotal->setCurPage($page)->setPageSize($pageSize)->load();

            foreach ($collectionTotal as $product) {

                $_product = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getEntityId());
                $_product_helper = $objectManager->create('Magento\Catalog\Helper\Product');

                $customOptions = $objectManager->get('Magento\Catalog\Model\Product\Option')
                        ->getProductOptionCollection($_product);

                $hasOptions = 0;
                $options = $customOptions->getData();
                if (count($options)) {
                    $hasOptions = 1;
                }

                $list[] = array(
                    'id' => $product->getEntityId(),
                    'sku' => $product->getSku(),
                    'type' => $product->getTypeId(),
                    'name' => $_product->getName(),
                    'regular_price' => $_product->getPrice(),
                    'final_price' => $_product->getFinalPrice(),
                    'description' => $_product->getDescription(),
                    'short_description' => $_product->getShortDescription(),
                    'image' => $_product_helper->getThumbnailUrl($_product),
                    'is_salable' => $product->getIsSalable(),
                    'has_options' => $hasOptions,
                    'options' => $options
                );
            }
        }

        return array(array("product_count" => $total, "total_page" => $collectionTotal->getLastPageNumber(), "data" => $list));
    }

}

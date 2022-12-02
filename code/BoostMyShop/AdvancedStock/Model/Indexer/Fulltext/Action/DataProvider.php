<?php
namespace BoostMyShop\AdvancedStock\Model\Indexer\Fulltext\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;
use Magento\Framework\App\ObjectManager;

class DataProvider extends \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider
{
    private $searchableAttributes;
    private $separator = ' | ';
    private $productTypes = [];
    private $productEmulators = [];
    private $productAttributeCollectionFactory;
    private $eavConfig;
    private $catalogProductType;
    private $eventManager;
    private $storeManager;
    private $engine;
    private $resource;
    private $connection;
    private $metadata;
    private $attributeOptions = [];
    private $searchableAttributesByBackendType = [];
    private $antiGapMultiplier;
    private $stockConfiguration;
    private $stockStatusRepository;

    public function __construct(
        ResourceConnection $resource,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttributeCollectionFactory,
        \Magento\CatalogSearch\Model\ResourceModel\EngineProvider $engineProvider,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        int $antiGapMultiplier = 5
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->catalogProductType = $catalogProductType;
        $this->eavConfig = $eavConfig;
        $this->productAttributeCollectionFactory = $prodAttributeCollectionFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->engine = $engineProvider->get();
        $this->metadata = $metadataPool->getMetadata(ProductInterface::class);
        $this->antiGapMultiplier = $antiGapMultiplier;
        $this->productMetadata = $productMetadata;
        parent::__construct($resource, $catalogProductType, $eavConfig, $prodAttributeCollectionFactory, $engineProvider, $eventManager, $storeManager, $metadataPool, $antiGapMultiplier);

    }


    public function prepareProductIndex($indexData, $productData, $storeId)
    {
        $index = [];

        if (version_compare($this->productMetadata->getVersion(), '2.3.0', '>'))
            $indexData = $this->filterOutOfStockProducts($indexData, $storeId);
        else
            return parent::prepareProductIndex($indexData, $productData, $storeId);

        foreach ($this->getSearchableAttributes('static') as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (isset($productData[$attributeCode])) {
                if ('store_id' === $attributeCode) {
                    continue;
                }

                    $value = $this->getAttributeValue($attribute->getId(), $productData[$attributeCode], $storeId);
                if ($value) {
                    if (isset($index[$attribute->getId()])) {
                        if (!is_array($index[$attribute->getId()])) {
                            $index[$attribute->getId()] = [$index[$attribute->getId()]];
                        }
                        $index[$attribute->getId()][] = $value;
                    } else {
                        $index[$attribute->getId()] = $value;
                    }
                }
            }
        }
        foreach ($indexData as $entityId => $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValues) {
                $value = $this->getAttributeValue($attributeId, $attributeValues, $storeId);
                if (!empty($value)) {
                    if (isset($index[$attributeId])) {
                        $index[$attributeId][$entityId] = $value;
                    } else {
                        $index[$attributeId] = [$entityId => $value];
                    }
                }
            }
        }

        $product = $this->getProductEmulator(
            $productData['type_id']
        )->setId(
            $productData['entity_id']
        )->setStoreId(
            $storeId
        );
        $typeInstance = $this->getProductTypeInstance($productData['type_id']);
        $data = $typeInstance->getSearchableData($product);
        if ($data) {
            $index['options'] = $data;
        }

        return $this->engine->prepareEntityIndex($index, $this->separator);
    }


    private function filterOutOfStockProducts($indexData, $storeId): array
    {
        if (!$this->getStockConfiguration()->isShowOutOfStock($storeId)) {
            $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();

            $productIds = array_keys($indexData);
            $stockStatusCriteria = $this->createStockStatusCriteria();
            $stockStatusCriteria->setProductsFilter($productIds);
            $stockStatusCriteria->setScopeFilter($websiteId);
            $stockStatusCollection = $this->getStockStatusRepository()->getList($stockStatusCriteria);
            $stockStatuses = $stockStatusCollection->getItems();
            $stockStatuses = array_filter($stockStatuses, function (StockStatusInterface $stockStatus) {
                return StockStatusInterface::STATUS_IN_STOCK == $stockStatus->getStockStatus();
            });
            $indexData = array_intersect_key($indexData, $stockStatuses);
        }
        return $indexData;
    }

    private function createStockStatusCriteria()
    {
        return ObjectManager::getInstance()->create(StockStatusCriteriaInterface::class);
    }

    private function getStockStatusRepository()
    {
        if (null === $this->stockStatusRepository) {
            $this->stockStatusRepository = ObjectManager::getInstance()->get(StockStatusRepositoryInterface::class);
        }
        return $this->stockStatusRepository;
    }

    private function getAttributeValue($attributeId, $valueIds, $storeId)
    {
        $attribute = $this->getSearchableAttribute($attributeId);
        $value = $this->engine->processAttributeValue($attribute, $valueIds);
        if (false !== $value) {
            $optionValue = $this->getAttributeOptionValue($attributeId, $valueIds, $storeId);
            if (null === $optionValue) {
                $value = $this->filterAttributeValue($value);
            } else {
                $value = implode($this->separator, array_filter([$value, $optionValue]));
            }
        }

        return $value;
    }

    private function filterAttributeValue($value)
    {
        return preg_replace('/\s+/iu', ' ', trim(strip_tags($value)));
    }

    private function getProductEmulator($typeId)
    {
        if (!isset($this->productEmulators[$typeId])) {
            $productEmulator = new \Magento\Framework\DataObject();
            $productEmulator->setTypeId($typeId);
            $this->productEmulators[$typeId] = $productEmulator;
        }
        return $this->productEmulators[$typeId];
    }

    private function getProductTypeInstance($typeId)
    {
        if (!isset($this->productTypes[$typeId])) {
            $productEmulator = $this->getProductEmulator($typeId);

            $this->productTypes[$typeId] = $this->catalogProductType->factory($productEmulator);
        }
        return $this->productTypes[$typeId];
    }

    private function getStockConfiguration()
    {
        if (null === $this->stockConfiguration) {
            $this->stockConfiguration = ObjectManager::getInstance()->get(StockConfigurationInterface::class);
        }
        return $this->stockConfiguration;
    }

    private function getAttributeOptionValue($attributeId, $valueIds, $storeId)
    {
        $optionKey = $attributeId . '-' . $storeId;
        $attributeValueIds = explode(',', $valueIds);
        $attributeOptionValue = '';
        if (!array_key_exists($optionKey, $this->attributeOptions)
        ) {
            $attribute = $this->getSearchableAttribute($attributeId);
            if ($this->engine->allowAdvancedIndex()
                && $attribute->getIsSearchable()
                && $attribute->usesSource()
            ) {
                $attribute->setStoreId($storeId);
                $options = $attribute->getSource()->toOptionArray();
                $this->attributeOptions[$optionKey] = array_column($options, 'label', 'value');
                $this->attributeOptions[$optionKey] = array_map(function ($value) {
                    return $this->filterAttributeValue($value);
                }, $this->attributeOptions[$optionKey]);
            } else {
                $this->attributeOptions[$optionKey] = null;
            }
        }
        foreach ($attributeValueIds as $attrValueId) {
            if (isset($this->attributeOptions[$optionKey][$attrValueId])) {
                $attributeOptionValue .= $this->attributeOptions[$optionKey][$attrValueId] . ' ';
            }
        }
        return empty($attributeOptionValue) ? null : trim($attributeOptionValue);
    }
}

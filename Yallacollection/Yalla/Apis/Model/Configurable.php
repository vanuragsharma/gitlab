<?php

/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Yalla\Apis\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProTypeModel;

class Configurable implements \Yalla\Apis\Api\ConfigurableInterface {

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $_configurableProTypeModel;

    public function __construct(ProductFactory $productFactory, ConfigurableProTypeModel $configurableProTypeModel) {
        $this->productFactory = $productFactory;
        $this->_configurableProTypeModel = $configurableProTypeModel;
    }

    /**
     * {@inheritdoc}
     */
    public function configurableOptions() {
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
        $postData = file_get_contents("php://input");
        $request = [];
        if (!empty($postData)) {
            $request = json_decode($postData, true);
        }

        $productId = $request['product_id'];
        $optionIds = $request['attr_val'];
        $attributeIds = $request['attr_id'];

        $storeId = null;
        $editMode = false;

        $options_list = explode(',', $optionIds);
        $attributes_list = explode(',', $attributeIds);

        try {

            $product = $this->productFactory->create();
            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            if ($storeId !== null) {
                $product->setData('store_id', $storeId);
            }
            $product->load($productId);
            if (!$product->getId()) {
                echo json_encode(['success' => 'false', 'msg' => __('Requested product doesn\'t exist')]);
                exit;
            }

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $data = [];
            if ($product->getTypeId() == 'configurable') {

                $options_data = $product->getTypeInstance()->getConfigurableOptions($product);

                if (count($attributes_list) == 0) {
                    echo json_encode(array('success' => 'false', 'msg' => 'Select an option'));
                    exit;
                }

                if (count($options_list) == 0) {
                    echo json_encode(array('success' => 'false', 'msg' => 'Select an option'));
                    exit;
                }

                if (count($attributes_list) == count($options_data) && count($attributes_list) == count($options_list)) {

                    $i = 0;
                    $attributeValues = [];
                    foreach ($attributes_list as $attribute_id) {
                        $attributeValues[$attribute_id] = $options_list[$i];
                        $i++;
                    }

                    $quantity = 0;
                    $assPro = $this->_configurableProTypeModel->getProductByAttributes($attributeValues, $product);
                    $assocateProId = $assPro->getEntityId();

                    // get stock of associated product
                    $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($assPro->getId());
                    $quantity = $productStockObj->getData('qty');

                    // Check if product is not enabled
                    $status = $assPro->getStatus();

                    if (!$status) {
                        $quantity = 0;
                    }

                    echo json_encode(array('success' => 'true', 'msg' => 'Success', 'collection' => ['attributes' => [], 'child_id' => $assocateProId, 'qty' => (int) $quantity]));
                    exit;
                }

                // Find index of selected attribute's option
                foreach ($options_data as $key => $attr) {

                    if ($key == $attributes_list['0']) {
                        $selected_option_indexes = [];
                        $i = 0;
                        foreach ($attr as $p) {

                            if ($p['value_index'] == $options_list[0]) {
                                $selected_option_indexes[] = $p['sku'];
                            }
                            $i++;
                        }
                    }
                }


                // Find available options of another attribute
                foreach ($options_data as $key => $attr) {

                    if ($key != $attributes_list['0']) {
                        $configurable_options = ['attribute_id' => $key];

                        $j = 0;

                        foreach ($attr as $p) {
                            if (!isset($configurable_options['type'])) {
                                $configurable_options['type'] = $p['attribute_code'];
                                $configurable_options['attribute_code'] = $p['attribute_code'];
                            }

                            if (in_array($p['sku'], $selected_option_indexes)) {
                                $option_swatch = ['value' => $p['default_title'], 'option_id' => $p['value_index']];
                                $configurable_options['attributes'][$p['value_index']] = $option_swatch;
                            }
                            $j++;
                        }
                        // Remove keys from attributes array
                        $array_without_key = [];

                        foreach ($configurable_options['attributes'] as $attributes) {
                            $array_without_key[] = $attributes;
                        }
                        $configurable_options['attributes'] = $array_without_key;
                        $data[] = $configurable_options;
                    }
                }
            }
        } catch (\Exception $ex) {
            echo json_encode(array('success' => 'false', 'msg' => $ex->getMessage()));
            exit;
        }
        echo json_encode(array('success' => 'true', 'msg' => 'Success', 'collection' => $data));
        exit;
    }

}


<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

class Save extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    protected function _filterPostData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            ['po_eta' => $this->_dateFilter, 'po_payment_date' => $this->_dateFilter, 'po_invoice_date' => $this->_dateFilter],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();

        if(isset($data['products']))
        {
            foreach($data['products'] as $index => $product)
            {
                if(isset($product['eta']) && $product['eta'] != "")
                {
                    $productsInputFilter = new \Zend_Filter_Input(
                        ['eta' => $this->_dateFilter],
                        [],
                        $product
                    );
                    $data['products'][$index] = $productsInputFilter->getUnescaped();
                }
            }
        }

        return $data;
    }

    public function execute()
    {
        $poId = (int)$this->getRequest()->getParam('po_id');
        $currentTab = str_replace('page_tabs_', '', $this->getRequest()->getParam('current_tab'));
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            $this->_redirect('adminhtml/*/');
            return;
        }

        /** @var $model \Magento\User\Model\User */
        $model = $this->_orderFactory->create()->load($poId);
        if ($poId && $model->isObjectNew()) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_redirect('supplier/order/index');
            return;
        }

        try
        {
            $data = $this->_filterPostData($data);
            $origData = $model->getData();
            $data = array_merge($origData, $data);
            $model->setData($data);
            $model->save();

            if(isset($data['po_status']) && isset($origData['po_status']) && $data['po_status'] != $origData['po_status'])
                $model->addHistory(__('Status updated from %1 to %2', $origData['po_status'], $data['po_status']));

            if(isset($data['po_eta']) && isset($origData['po_eta']) && date('Y-m-d', strtotime($data['po_eta'])) != date('Y-m-d', strtotime($origData['po_eta'])))
                $model->addHistory(__('ETA updated from %1 to %2', date('Y-m-d', strtotime($origData['po_eta'])), date('Y-m-d', strtotime($data['po_eta']))));

            foreach($model->getAllItems() as $item)
            {
                if (isset($data['products'][$item->getId()]))
                    $this->updateOrderProduct($model, $item, $data['products'][$item->getId()]);
            }

            //add products to PO
            if (isset($data['selected_product'])) {
                $method = ($this->_config->getSetting('general/pack_quantity')) ? 'pack_qty' : 'products_to_add';
                $this->addProducts($model, $data['selected_product'], $method);
                $currentTab = 'products_section';
            }

            $delimiter = isset($data['delimiter']) ? $data['delimiter'] : ';';
            $this->processImport($model, $poId, $delimiter);

            if (isset($_FILES['po_shipping_label_pdf']) && isset($_FILES['po_shipping_label_pdf']['name']) && strlen($_FILES['po_shipping_label_pdf']['name'])) {
                $this->importShippingLabel($model);
            }

            $model->updateDeliveryProgress();
            $model->updateTotals();

            //add new history
            if (isset($data['new_history']) && $data['new_history'] != "")
                $model->addHistory($data['new_history']);

            //todo : perform these actions only if really needed !
            $model->updateQtyToReceive();
            $model->updateExtendedCosts();
            $model->updateMissingPrices();

            // add history
            if (!$poId)
                $description = __("Purchase order created");
            else
                $description = __('Purchase order updated');

            //reload order and update totals again to prevent update issue due to the discount (bad fix)
            if ($poId)
            {
                $model = $this->_orderFactory->create()->load($poId);
                $model->updateTotals();
            }

            $this->messageManager->addSuccess(__('You saved the order.'));
            $this->_redirect('*/*/Edit', ['po_id' => $model->getId(), 'active_tab' => $currentTab]);
            $model->addHistory($description);

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Validator\Exception $e) {



            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->redirectToEdit($model, $data);
        }
    }

    /**
     * Update single order product from post data
     *
     * @param $orderProduct
     * @param $data
     */
    protected function updateOrderProduct($model, $orderProduct, $data)
    {
        if (isset($data['remove']))
            $orderProduct->delete();
        else
        {
            //qty
            if($orderProduct->getPopQty() != $data['qty'])
                $model->addHistory(__('%1 of product %2 updated from %3 to %4', 'Qty', $this->getProductSku($orderProduct['pop_sku']), $orderProduct->getPopQty(), $data['qty']));
            $orderProduct->setPopQty($data['qty']);

            //buying price
            if($orderProduct->getPopPrice() != $data['price'])
                $model->addHistory(__('%1 of product %2 updated from %3 to %4', 'Buying price', $this->getProductSku($orderProduct['pop_sku']), $orderProduct->getPopPrice(), $data['price']));
            $orderProduct->setPopPrice($data['price']);

            //discount
            if (isset($data['discount']))
            {
                if($orderProduct->getPopDiscountPercent() != $data['discount'])
                    $model->addHistory(__('%1 of product %2 updated from %3% to %4%', 'Discount', $this->getProductSku($orderProduct['pop_sku']), $orderProduct->getPopDiscountPercent(), $data['discount']));
                $orderProduct->setPopDiscountPercent($data['discount']);
            }

            //tax rate
            if($orderProduct->getPopTaxRate() != $data['tax_rate'])
                $model->addHistory(__('%1 of product %2 updated from %3% to %4%', 'Tax rate', $this->getProductSku($orderProduct['pop_sku']), $orderProduct->getPopTaxRate(), $data['tax_rate']));
            $orderProduct->setPopTaxRate($data['tax_rate']);

            //supplier sku
            if($orderProduct->getPopSupplierSku() != $data['supplier_sku'])
                $model->addHistory(__('%1 of product %2 updated from %3 to %4', 'Supplier sku', $this->getProductSku($orderProduct['pop_sku']), $orderProduct->getPopSupplierSku(), $data['supplier_sku']));
            $orderProduct->setPopSupplierSku($data['supplier_sku']);

            //eta
            if(isset($data['eta']))
            {
                if(date("Y-m-d", strtotime($orderProduct->getPopEta())) != date("Y-m-d", strtotime($data['eta'])))
                    $model->addHistory(__('%1 of product %2 updated from %3 to %4', 'ETA', $this->getProductSku($orderProduct['pop_sku']),$orderProduct->getPopEta() ? date("Y-m-d", strtotime($orderProduct->getPopEta())) : __('none'), $data['eta'] ? : __('none')));
                $orderProduct->setPopEta($data['eta']);
            }
            if(array_key_exists('qty_pack', $data))
            {
                if($orderProduct->getPopQtyPack() != $data['qty_pack'])
                    $model->addHistory(__('%1 of product %2 updated from %3 to %4', 'Pack qty', $this->getProductSku($orderProduct['pop_sku']), $orderProduct->getPopQtyPack(), $data['qty_pack']));
                $orderProduct->setPopQtyPack($data['qty_pack']);
            }

            $orderProduct->save();
        }
    }

    protected function getProductSku($sku)
    {
        return $sku;
    }

    protected function addProducts($order, $productsToAdd, $method)
    {
        $hasChanges = false;
        $additional = array();

        foreach($productsToAdd as $productId => $productData)
        {
            if (isset($productData['qty']) && $productData['qty'] > 0)
            {
                $additional['buying_price'] = $productData['buying_price'];
                if($method == 'pack_qty') {
                    $additional['qty_pack'] = $productData['pack_qty'];
                }
                $order->addProduct($productId, $productData['qty'], $additional, $forcePriceUpdate = true);
                $hasChanges = true;
            }
        }

        return $hasChanges;
    }

    /**
     * @param \Magento\User\Model\User $model
     * @param array $data
     * @return void
     */
    protected function redirectToEdit(\BoostMyShop\Supplier\Model\Order $model, array $data)
    {
        $this->_getSession()->setUserData($data);
        $arguments = $model->getId() ? ['po_id' => $model->getId()] : [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => '']);
        $this->_redirect('supplier/order/edit', $arguments);


    }

    protected function processImport($model, $poId, $delimiter)
    {
        $importResult = null;
        try
        {
            $importResult = $this->_csvImport->checkPoImport($model, $poId, $delimiter);
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('Error: %1', $ex->getMessage()));
        }

        if(is_array($importResult) && !empty($importResult)){
            $found = array();
            $notFound = array();
            $error = array();
            foreach ($importResult as $key => $value) {
                if(array_key_exists('found', $value))
                    $found[] = $value['found'];

                if(array_key_exists('not_found', $value))
                    $notFound[] = $value['not_found'];

                if(array_key_exists('error', $value))
                    $error[] = $value['error'];
            }

            if(count($error) > 0)
                $this->messageManager->addError(__('sku or qty is missing in %1 row(s)', count($error)));

            if(count($notFound) > 0)
                $this->messageManager->addError(__('Sku "%1" unknown', implode(", ", $notFound)));

            if(count($found) > 0)
                $this->messageManager->addSuccess(__('Csv file has been imported : %1 row(s) processed', count($found)));
        }

    }

    protected function importShippingLabel($order)
    {
        try {
            $baseDirectory = 'purchase_order/shipping_label';
            $uploader = $this->_uploader->create(['fileId' => 'po_shipping_label_pdf']);
            $uploader->setAllowedExtensions(['pdf']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $mediaDirectory = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $result = $uploader->save(
                $mediaDirectory->getAbsolutePath($baseDirectory)
            );

            $order->setpo_shipping_label_path($result['file'])->save();

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

}

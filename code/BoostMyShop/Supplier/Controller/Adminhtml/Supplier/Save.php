<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Supplier;

class Save extends \BoostMyShop\Supplier\Controller\Adminhtml\Supplier
{
    public function execute()
    {

        $supId = (int)$this->getRequest()->getParam('sup_id');
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            $this->_redirect('adminhtml/*/');
            return;
        }

        /** @var $model \Magento\User\Model\User */
        $model = $this->_supplierFactory->create()->load($supId);
        if ($supId && $model->isObjectNew()) {
            $this->messageManager->addError(__('This supplier no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }

        if(isset($data['sup_delayed_notification_hours']))
        {
            $data['sup_delayed_notification_hours'] = implode(",", $data['sup_delayed_notification_hours']);
        }

        $model->setData($data);

        if (isset($data['products']))
        {
            foreach($data['products'] as $spId => $productData)
                $this->updateProduct($spId, $productData);
        }
        $this->_eventManager->dispatch('supplier_edit_save', ['supplier' => $model, 'post_data' => $data, 'message_manager' => $this->messageManager]);
        /** Before updating admin user data, ensure that password of current admin user is entered and is correct */
        try {
            $model->save();
            $this->messageManager->addSuccess(__('You saved the supplier.'));
            $this->_redirect('*/*/Edit', ['sup_id' => $model->getId()]);
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
     * @param
     * @param array $data
     * @return void
     */
    protected function redirectToEdit(\BoostMyShop\Supplier\Model\Supplier $model, array $data)
    {
        $this->_getSession()->setUserData($data);
        $arguments = $model->getId() ? ['sup_id' => $model->getId()] : [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => '']);
        $this->_redirect('adminhtml/*/edit', $arguments);
    }

    protected function updateProduct($spId, $productData)
    {
        $obj = $this->_supplierProductFactory->create()->load($spId);
        $obj->setsp_sku($productData['sku']);
        $obj->setsp_price($productData['price']);
        $obj->setsp_primary($productData['primary']);
        $obj->save();

        return $this;
    }

}

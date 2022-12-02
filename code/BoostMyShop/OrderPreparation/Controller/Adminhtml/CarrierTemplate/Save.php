<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\CarrierTemplate;

class Save extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\CarrierTemplate
{
    public function execute()
    {

        $supId = (int)$this->getRequest()->getParam('ct_id');
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            $this->_redirect('adminhtml/*/');
            return;
        }

        /** @var $model \Magento\User\Model\User */
        $model = $this->_carrierTemplateFactory->create()->load($supId);
        if ($supId && $model->isObjectNew()) {
            $this->messageManager->addError(__('This template no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }

        $serializedCodes = ['ct_shipping_methods', 'ct_warehouse_ids','ct_store_ids', 'ct_custom', 'ct_cost_matrix', 'ct_disable_labels_pregeneration'];
        foreach($serializedCodes as $serializedCode)
        {
            if (isset($data[$serializedCode]))
                $data[$serializedCode] = serialize($data[$serializedCode]);
            else
                $data[$serializedCode] = serialize([]);
        }

        $model->setData($data);


        try {
            $model->save();

            //check carrier template connection
            if ($model->getRenderer())
                $model->getRenderer()->checkConnection($model);

            $this->messageManager->addSuccess(__('You saved the template.'));
            $this->_redirect('*/*/Edit', ['ct_id' => $model->getId()]);
        } catch (\Magento\Framework\Validator\Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->redirectToEdit($model, $data);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->redirectToEdit($model, $data);
        }

    }

    /**
     * @param
     * @param array $data
     * @return void
     */
    protected function redirectToEdit($model, array $data)
    {
        $this->_getSession()->setUserData($data);
        $arguments = $model->getId() ? ['ct_id' => $model->getId()] : [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => '']);
        $this->_redirect('*/*/Edit', $arguments);
    }

}

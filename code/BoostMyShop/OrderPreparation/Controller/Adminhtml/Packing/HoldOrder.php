<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing;

use Magento\Backend\App\Area\FrontNameResolver;


class HoldOrder extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Packing
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $id = $this->getRequest()->getParam('order_id');
        $model = $this->_inProgressFactory->create()->load($id);
        $this->_coreRegistry->register('current_inprogress', $model);

        try {
                $order = $model->getOrder();

                $model->delete();
                if($order->getStatus() !== \Magento\Sales\Model\Order::STATE_HOLDED)
                    $order->hold()->save();

                $note = $this->getRequest()->getParam('note');
                if($note){
                    if($this->_configFactory->create()->isOrganizerModuleInstall()){
                        $this->addOrganizer($order, $note);
                    }

                }
        } catch (\Exception $exception) {
            /** @var array $response */
            $response = [
                'message' => __('An error occurred : '.$exception->getMessage()),
                'stack_trace' => $exception->getTraceAsString()
            ];
        }
    }

    protected function addOrganizer($order, $txt)
    {
        $userId = $this->_preparationRegistry->getCurrentOperatorId() ? : -1;
        $organizer = $this->getObjectManager()->create('BoostMyShop\Organizer\Model\Organizer');
        $organizer->seto_author_user_id($userId)
            ->seto_title(__('order holded from order preparation'))
            ->seto_comments($txt)
            ->seto_category()
            ->seto_status('New')
            ->seto_object_type(\BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_ORDER)
            ->seto_object_id($order->getId())
            ->seto_created_at($this->getObjectManager()->create('Magento\Framework\Stdlib\DateTime\DateTime')->gmtDate())
            ->seto_object_description('Order '.$order->getincrement_id())
            ->save();
        return $organizer;
    }

    protected function getObjectManager()
    {
        if (null == $this->objectManager) {
            $area = FrontNameResolver::AREA_CODE;
            $this->_configScope = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Config\ScopeInterface::class);
            $this->_configScope->setCurrentScope($area);
            $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }
        return $this->objectManager;
    }

    protected function _isAllowed()
    {
        return true;
    }

}

<?php

namespace BoostMyShop\OrderPreparation\Block\Batch\Edit\Tab\Renderer;

use Magento\Framework\DataObject;

class Actions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(DataObject $object)
    {
        $deleteUrl = $this->getUrl('orderpreparation/batch/removeorder', ['bob_id' => $object->getip_batch_id(), 'ip_id' => $object->getId()]);
        $html = '<br><a href="#" onclick="removeBatchOrder(\''.$deleteUrl.'\')" >'.__('Remove').'</a>';

        return $html;
    }
}
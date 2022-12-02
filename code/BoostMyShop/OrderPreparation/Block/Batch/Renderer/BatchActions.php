<?php

namespace BoostMyShop\OrderPreparation\Block\Batch\Renderer;

use Magento\Framework\DataObject;

class BatchActions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_batchHelper = null;

    public function __construct(\Magento\Backend\Block\Context $context,
        \BoostMyShop\OrderPreparation\Model\BatchHelper $batchHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_batchHelper = $batchHelper;
    }

    public function render(DataObject $batch)
    {
        $url = $this->getUrl('orderpreparation/batch/view', ['bob_id' => $batch->getId()]);
        $printUrl = $this->getUrl('orderpreparation/batch/printaction', ['bob_id' => $batch->getId()]);
        $closeUrl = $this->getUrl('orderpreparation/batch/closeaction', ['bob_id' => $batch->getId()]);
        $typeInstance = $this->_batchHelper->getTypeInstance($batch->getbob_type());

        $html = '<a class="batch_detail_popup" href="#" data-href="'.$url.'"  data-id="'.$batch->getId().'" data-label ="'.$batch->getbob_label().'">'.__('View').'</a>';
        $html .= '<br><a href="'.$printUrl.'" >'.__('Print').'</a>';

        if($batch->getbob_status() != \BoostMyShop\OrderPreparation\Model\Batch::STATUS_COMPLETE)
            $html .= '<br><a href="#" onclick="closeBatch(\''.$closeUrl.'\')" >'.__('Close').'</a>';

        $actions = $typeInstance->getAdditionalActions($batch);
        foreach($actions as $action)
        {
            $html .= '<br><a onclick="'.$action['onclick'].'"  data-id="'.$batch->getId().'" href="'.$action['url'].'" target="'.$action['target'].'">'.$action['label'].'</a>';
        }
        if($batch->getbob_status() == \BoostMyShop\OrderPreparation\Model\Batch::STATUS_READY_FOR_LABEL_GENERATION
            || $batch->getbob_status() == \BoostMyShop\OrderPreparation\Model\Batch::STATUS_READY
            || $batch->getbob_status() == \BoostMyShop\OrderPreparation\Model\Batch::STATUS_PRINTED
        ){
            $deleteUrl = $this->getUrl('orderpreparation/batch/delete', ['bob_id' => $batch->getId()]);
            $html .= '<br><a href="#" onclick="deleteBatch(\''.$deleteUrl.'\')" >'.__('Delete').'</a>';
        }

        return $html;
    }
}
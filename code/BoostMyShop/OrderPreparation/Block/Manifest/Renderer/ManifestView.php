<?php

namespace BoostMyShop\OrderPreparation\Block\Manifest\Renderer;

use Magento\Framework\DataObject;

class ManifestView extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(DataObject $row)
    {
        $url = $this->getUrl('orderpreparation/manifest/view', ['bom_id' => $row->getId()]);
        $html = '<a class="manifest_detail_popup" href="#" data-href="'.$url.'"  data-id="'.$row->getId().'">'.__('View').'</a>';

        return $html;
    }
}
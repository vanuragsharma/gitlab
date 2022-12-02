<?php

namespace BoostMyShop\OrderPreparation\Block\Manifest\Renderer;

use Magento\Framework\DataObject;

class ManifestActions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_carrierTemplateCollectionFactory;
    protected $_carrierTemplate = false;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate\CollectionFactory $carrierTemplateCollectionFactory,
                                array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_carrierTemplateCollectionFactory = $carrierTemplateCollectionFactory;
    }

    public function render(DataObject $row)
    {
        $html = '';
        $url = $this->getUrl('*/manifest/download', ['id' => $row->getbom_id()]);
        $html .= '<center><input type="button" value="'.__('Print').'" onclick="document.location.href = \''.$url.'\';"></center>';

        if($row->getbom_edi_status() == \BoostMyShop\OrderPreparation\Model\Manifest::STATUS_SENT)
            $content = 'Send again EDI';
        else
            $content = 'Send EDI';

        if($this->isSupportManifestEdi($row->getbom_carrier(), $row->getbom_warehouse_id()))
            $html .= '<center><input type="button" value="'.$content.'" class="manifest_export" id="manifest_export_'.$row->getbom_id().'" data-manifest-export-url="'.$this->getManifestExportUrl().'" data-carrier-id="'.$this->_carrierTemplate->getId().'"  data-manifest-id="'.$row->getbom_id().'" ></center>';

        $this->_carrierTemplate = false;

        return $html;
    }

    public function getManifestExportUrl()
    {
        return $this->getUrl('orderpreparation/manifest/export');
    }

    public function isSupportManifestEdi($carrier, $warehouseId) {
        $result = false;

        $this->loadCarrierTemplate($carrier, $warehouseId);
        if($this->_carrierTemplate)
            $result = $this->_carrierTemplate->getRenderer()->supportManifestEdi();

        return $result;
    }

    public function loadCarrierTemplate($carrier, $warehouseId) {
        if(!$this->_carrierTemplate) {
            $template = $this->_carrierTemplateCollectionFactory->create()
                ->addActiveFilter()
                ->addShippingMethodFilter($carrier)
                ->addWarehouseFilter($warehouseId)
                ->getFirstItem();

            if($template)
                $this->_carrierTemplate = $template;
        }

        return $this->_carrierTemplate;
    }
}
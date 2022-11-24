<?php

namespace Yalla\Vendors\Block\Adminhtml\Vendors\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Yalla\Vendors\Model\System\Config\Status;
use Magento\Backend\Model\UrlInterface;
use Yalla\Vendors\Helper\Data;
use Yalla\Vendors\Model\VendorProductsFactory;

class Info extends Generic implements TabInterface {

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Yalla\ s\Model\Config\Status
     */
    protected $bannerStatus;

    /**
     * @var \Magento\Backend\Model\UrlInterface,
     */
    protected $_url;

    /**
     * @var Yalla\Vendors\Helper\Data
     */
    protected $_helper;

    /**
     * Vendor Products model factory
     *
     * @var \Yalla\Vendors\Model\VendorProductsFactory
     */
    protected $_vendorProductsFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param Status $bannerStatus
     * @param array $data
     */
    public function __construct(
    Context $context, Registry $registry, FormFactory $formFactory, Config $wysiwygConfig, Status $bannerStatus, UrlInterface $_url, Data $helper, VendorProductsFactory $vendorProductsFactory, array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->bannerStatus = $bannerStatus;
        $this->_url = $_url;
        $this->_helper = $helper;
		$this->_vendorProductsFactory = $vendorProductsFactory;
		
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm() {
        /** @var $model \Yalla\Vendors\Model\Vendors*/
        $model = $this->_coreRegistry->registry('yalla_vendor');
        $data = $model->getData();
	
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('vendors_');
        $form->setFieldNameSuffix('vendors');

        $fieldset = $form->addFieldset(
                'base_fieldset', ['legend' => __('General')]
        );

      if ($model->getId()) {
            $fieldset->addField(
                    'vendor_id', 'hidden', ['name' => 'vendor_id']
            );
        }
       	$fieldset->addField(
            'vendor_products', 'hidden', ['name' => 'vendor_products']
        );
        
        $fieldset->addField(
            'vendor_name', 'text', [
            'name' => 'vendor_name',
            'label' => __('Vendor Name'),
		'placeholder' => 'Enter vendors name here',
            'required' => true
                ]
        );
	$fieldset->addField(
            'vendor_email', 'text', [
            'name' => 'vendor_email',
            'label' => __('Vendor Email'),
		'placeholder' => 'Enter vendor email here',
            'required' => true
                ]
        )->setAfterElementHtml('
		    <script>
		        require([
		             "jquery",
		        ], function($){
		            $(document).ready(function () {
		            	$(\'#vendors_edit_tabs_address_info_content\').on(\'click\', \'[name="vp_id[]"]\', function(){
                                    let current_ids = $("#vendors_vendor_products").val();
                                    if($(this).prop("checked") == true){
                                        if(current_ids !== ""){
                                            current_ids = current_ids + $(this).val() + ",";
                                            $("#vendors_vendor_products").val(current_ids);
                                        }else{
                                            $("#vendors_vendor_products").val(","+$(this).val()+",");
                                        }
                                    }else{
                                        var new_ids = current_ids.replace(","+$(this).val()+",", ",");
                                        $("#vendors_vendor_products").val(new_ids);
                                    }
                                });
                                
                                $(\'#vendors_edit_tabs_address_info_content\').on(\'click\', \'.action-next, .action-previous, .admin__filter-actions .action-default\', function(){
                                    isGridRefreshed();
                                });
                                $(\'#vendors_edit_tabs_address_info_content\').on(\'keypress\', \'.data-grid-filters .input-text\', function(e){
                                        if (e.keyCode == 13) {
                                            isGridRefreshed();
                                        }
                                });
		            });
                            
                            function isGridRefreshed(){
                                if($(".loading-mask").length){
                                    if($(".loading-mask").is(":visible")){
                                        setTimeout(isGridRefreshed, 500);
                                        return false;
                                    }
                                }
                                
                                var current_ids = $("#vendors_vendor_products").val();
                                
                                $(\'[name="vp_id[]"]\').prop("checked", false);
                                $.each(current_ids.split(","), function(){
                                	console.log("this", this);
                                    if(this !== ""){
                                        $("#id_"+this).prop( "checked", true );
                                    }
                                })
                            }
		          });
		   </script>
		');
	$fieldset->addField(
            'vendor_number', 'text', [
            'name' => 'vendor_number',
            'label' => __('Vendor Number'),
		'placeholder' => 'Enter vendor number here',
            'required' => true
                ]
        );
	$fieldset->addField(
            'vendor_address', 'text', [
            'name' => 'vendor_address',
            'label' => __('Vendor Address'),
		'placeholder' => 'Enter vendor address here',
            'required' => true
                ]
        );
	   
        $fieldset->addField(
                'status', 'select', [
            'name' => 'status',
            'label' => __('Status'),
            'options' => $this->bannerStatus->toOptionArray()
                ]
        );

		if(isset($data['vendor_id'])){
			$vendorProductsModel = $this->_vendorProductsFactory->create();
			$collection = $vendorProductsModel->getCollection();
			$collection->addFieldToFilter('vendor_id', $data['vendor_id']);
			$product_ids = [];
			foreach($collection as $products){
				if($products->getProductId()){
					$product_ids[] = $products->getProductId();
				}
			}
			if(count($product_ids)){
				$data['vendor_products'] = ",".implode(',', $product_ids).",";
			}
		}
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel() {
        return __('Vendor Info');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return __('Vendor Info');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab() {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden() {
        return false;
    }

}

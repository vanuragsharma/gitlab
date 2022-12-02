<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace BoostMyShop\Supplier\Block;

/**
 * User block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Supplier extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var \Magento\User\Model\ResourceModel\User
     */
    protected $_resourceModel;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\User\Model\ResourceModel\User $resourceModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        //$this->_resourceModel = $resourceModel;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addData(
            [
                \Magento\Backend\Block\Widget\Container::PARAM_CONTROLLER => 'supplier',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BLOCK_GROUP => 'BoostMyShop_Supplier',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BUTTON_NEW => __('Add New Supplier'),
                \Magento\Backend\Block\Widget\Container::PARAM_HEADER_TEXT => __('Suppliers'),
            ]
        );
        parent::_construct();
        $this->_addNewButton();
        $this->_addImportButton();
    }

    protected function _addImportButton()
    {
        $this->addButton(
            'import',
            [
                'label' => __('Import'),
                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/import') . '\')',
                'class' => ''
            ]
        );
    }

}

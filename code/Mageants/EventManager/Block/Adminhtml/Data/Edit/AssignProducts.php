<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Block\Adminhtml\Data\Edit;

    class AssignProducts extends \Magento\Backend\Block\Template
    {
        /**
         * Block template
         *
         * @var string
         */
        protected $_template = 'products/assign_products.phtml';

        /**
         * @var \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
         */
        protected $blockGrid;

        /**
         * @var \Magento\Framework\Registry
         */
        protected $registry;

        /**
         * @var \Magento\Framework\Json\EncoderInterface
         */
        protected $jsonEncoder;


        protected $_productCollectionFactory;

        /**
         * AssignProducts constructor.
         *
         * @param \Magento\Backend\Block\Template\Context $context
         * @param \Magento\Framework\Registry $registry
         * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
         * @param array $data
         */
        public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            \Magento\Framework\Registry $registry,
            \Magento\Framework\Json\EncoderInterface $jsonEncoder,
            \Mageants\EventManager\Model\ResourceModel\Eventdata\CollectionFactory $productCollectionFactory, //your custom collection
            array $data = []
        ) {
            $this->registry = $registry;
            $this->jsonEncoder = $jsonEncoder;
            $this->_productCollectionFactory = $productCollectionFactory;
            parent::__construct($context, $data);
        }

        /**
         * Retrieve instance of grid block
         *
         * @return \Magento\Framework\View\Element\BlockInterface
         * @throws \Magento\Framework\Exception\LocalizedException
         */
        public function getBlockGrid()
        {

            if (null === $this->blockGrid) {
                $this->blockGrid = $this->getLayout()->createBlock(
                    'Mageants\EventManager\Block\Adminhtml\Data\Edit\Tab\Product',
                    'category.product.grid'
                );
            }
            return $this->blockGrid;
        }

        /**
         * Return HTML of grid block
         *
         * @return string
         */
        public function getGridHtml()
        {

            return $this->getBlockGrid()->toHtml();
        }

        /**
         * @return string
         */
        public function getProductsJson()
    {
        $entity_id = $this->getRequest()->getParam('e_id');
        $productFactory = $this->_productCollectionFactory->create();
        $productFactory->addFieldToFilter('e_id', ['eq' => $entity_id]);
        $result = array();
        if (!empty($productFactory->getData())) {
            foreach ($productFactory->getData() as $Products) {
                
                //$result[$Products['productassign']] = '';
                $productIds = explode(",",$Products['productassign']);
                
                if (!empty($productIds)) {
                    foreach ($productIds as $productid) {
                        $result[$productid] = "";
                    }
                }
            }
            return $this->jsonEncoder->encode($result);
        }
        return '{}';
    }

        public function getItem()
        {
            return $this->registry->registry('my_item');
        }   
    }

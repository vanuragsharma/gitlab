<?php
 
namespace Yalla\Vendors\Block\Adminhtml\Vendors\Grid\Column\Renderer;
 
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Framework\Registry;
use Yalla\Vendors\Helper\Data; 

class Banner extends AbstractRenderer
{
    /**
     * @var Registry
     */
    protected $registry;
    
    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;
    
    /**
     * @var Yalla\Vendors\Helper\Data
     */
    protected $_helper;
    
    /**
     * Banner constructor.
     * @param AttributeFactory $attributeFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        AttributeFactory $attributeFactory,
        Context $context,
        Data $helper,
        array $data = array()
    )
    {
        $this->attributeFactory = $attributeFactory;
        $this->registry = $registry;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }
 
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        // Get default value:
        $value = parent::_getValue($row);
        $html = '';
        if(!empty($value)){
		
            $banner_url = $this->_helper->getBannerUrl($value);
            $html = '<img src="'.$banner_url.'" width="100px" />';
        }
        
        return $html;
    }
}

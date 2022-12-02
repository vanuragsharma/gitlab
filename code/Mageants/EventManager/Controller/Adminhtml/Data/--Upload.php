<?php
 /**
 * @category Mageants Product360Image
 * @package Mageants_Product360Image
 * @copyright Copyright (c) 2017 Mageants
 * @author Mageants Team <support@Mageants.com>
 */
namespace Mageants\EventManager\Controller\Adminhtml\Data;

use \Magento\Backend\App\Action\Context;
use Mageants\EventManager\Model\ResourceModel\Image;
use \Magento\Framework\Controller\Result\RawFactory;

class Upload extends \Magento\Backend\App\Action
{
    /**
     * Access Resource ID
     * 
     */
    const RESOURCE_ID = 'Mageants_Product360Image::product360_upload';
    /**
     * Upload model
     * 
     * @var \Mageants\Product360Image\Model\Upload
     */
    protected $_uploadModel;

    /**
     * Image model
     * 
     * @var \Mageants\Product360Image\Model\Product360\Image
     */
    protected $_imageModel;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        \Mageants\EventManager\Model\ImageUploader $uploadModel,
        Image $imageModel
    ) 
    {
        parent::__construct($context);
        
        $this->_imageModel = $imageModel;
        
        $this->_uploadModel = $uploadModel;
        
        $this->resultRawFactory = $resultRawFactory;
    }
     /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(Self::RESOURCE_ID);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
         try {
            
            $product_id = $this->getRequest()->getParam('id');
            //var_dump($product_id); exit;
            
            $data = array();
            
            $result = $this->_uploadModel->uploadFileAndGetName('image', $this->_imageModel->getBaseDir($product_id), $data);
            
            $result['url']= $this->_imageModel->getProduct360Url($product_id, $result['file']);
            
            unset($result['tmp_name']); 
        } 
        catch (\Exception $e) 
        {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        $response = $this->resultRawFactory->create();
        
        $response->setHeader('Content-type', 'text/plain');
        
        $response->setContents(json_encode($result)); 
        
        return $response;
    }
}

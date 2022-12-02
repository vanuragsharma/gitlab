<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ShowPriceAfterLogin
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ShowPriceAfterLogin\Controller\Adminhtml\AddAttributeValue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class AddCustomAttributeValue extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonResultFactory;
    
    /**
     * @var
     */
    protected $backendSession;

    /**
     * __construct function
     * @param \Magento\Backend\App\Action\Context $context,
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->backendSession = $context->getSession();
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        $data = $this->getRequest()->getParams();
        $this->backendSession->setCustomerGroupProductAttributeValue($data['values']);
        $response = true;
        return $result->setData($response);
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_ShowPriceAfterLogin::addattributevalue_addcustomattributevalue'
        );
    }
}

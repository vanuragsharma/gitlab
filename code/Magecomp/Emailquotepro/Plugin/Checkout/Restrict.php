<?php
namespace Magecomp\Emailquotepro\Plugin\Checkout;
 
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlFactory;
use Magento\Checkout\Controller\Index\Index;
use Magecomp\Emailquotepro\Helper\Data as EmailHelper;
 
class Restrict
{
    private $urlModel;
    private $resultRedirectFactory;
    private $messageManager;
    protected $emailHelper;
 
    public function __construct(
        UrlFactory $urlFactory,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        EmailHelper $emailHelper
    ) {
    
        $this->urlModel = $urlFactory;
        $this->resultRedirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->emailHelper = $emailHelper;
    }
 
    public function aroundExecute(
        Index $subject,
        \Closure $proceed
    ) {
        $this->urlModel = $this->urlModel->create();
        if ($this->emailHelper->isActive() && $this->emailHelper->getProceedToCheckoutButtonEnabled()=='1') {
         $defaultUrl = $this->urlModel->getUrl('checkout/cart/', ['_secure' => true]);
         $resultRedirect = $this->resultRedirectFactory->create();
         return $resultRedirect->setUrl($defaultUrl);
        }
        return $proceed();
    }
}
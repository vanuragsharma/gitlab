<?php

namespace BoostMyShop\OrderPreparation\Controller\Adminhtml;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Controller\Result\JsonFactory;

abstract class Manifest extends \Magento\Backend\App\AbstractAction
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_carrierTemplateFactory;
    protected $_objectManager;
    protected $resultJsonFactory;
    protected $resultPageFactory;
    protected $formKeyValidator;
    protected $_manifestHelper;
    protected $_manifestFactory;
    protected $_registry;
    protected $_filesystem;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplateFactory $carrierTemplateFactory,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \BoostMyShop\OrderPreparation\Helper\Manifest $manifestHelper,
        \BoostMyShop\OrderPreparation\Model\ManifestFactory $manifestFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->_carrierTemplateFactory = $carrierTemplateFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->_manifestHelper = $manifestHelper;
        $this->_manifestFactory = $manifestFactory;
        $this->_registry = $registry;
        $this->_filesystem = $filesystem;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('BoostMyShop_OrderPreparation::manifest');
    }
}
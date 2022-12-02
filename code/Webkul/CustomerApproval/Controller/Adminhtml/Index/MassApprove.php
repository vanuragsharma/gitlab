<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerApproval
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\CustomerApproval\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

class MassApprove extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    protected $helper;
    
    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository,
        \Webkul\CustomerApproval\Helper\Data $helper
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $customersUpdated = 0;
        foreach ($collection->getAllIds() as $customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $customer->setCustomAttribute("wk_customer_approval", '1');
            $this->customerRepository->save($customer);
            try {
                $this->helper->sendApprovalMail($customer);
                $customersUpdated++;
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Unable to send mail'));
            }

        }

        if ($customersUpdated) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $customersUpdated));
        }

        /**
         * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
         */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('customer/index/');
    }
}

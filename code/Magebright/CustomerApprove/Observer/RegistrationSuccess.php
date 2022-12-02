<?php
/**
 * @category  Magebright
 * @package   Magebright_CustomerApprove
 */

namespace Magebright\CustomerApprove\Observer;

use Magebright\CustomerApprove\Helper\Data;
use Magebright\CustomerApprove\Model\Approve;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionFactory;

class RegistrationSuccess implements ObserverInterface
{
    /**
     * @var \Magebright\CustomerApprove\Helper\Data
     */
    protected $helper;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerExtensionFactory
     */
    protected $customerExtensionFactory;

    /**
     * Constructor
     *
     * @param Data                        $helper
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerExtensionFactory    $customerExtensionFactory
     */
    public function __construct(
        Data $helper,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerExtensionFactory $customerExtensionFactory
    ) {
        $this->helper = $helper;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->customerExtensionFactory = $customerExtensionFactory;
    }

    /**
     * Set customer status on registration and notify admin
     * of the new registration.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return Magebright\CustomerApprove\Observer\RegistrationSuccess
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_customer = $observer->getCustomer();
        if(!$_customer || !$_customer->getId()) {
            return $this;
        }

        try {
            $approveStatus = Approve::PENDING;
            if($this->helper->canAutoApprove()) {
                $approveStatus = Approve::APPROVED;
            }
            $this->helper->saveApproveStatus($_customer, $approveStatus);


            if(!$this->helper->canNotifyAdmin()) {
                return $this;
            }

            $customer = $this->customerFactory->create()->load($_customer->getId());
            if(!$customer) {
                return $this;
            }

            $store = $this->helper->getStore();
            $templateData = [
                'customer' => $customer,
                'store' => $store
            ];

            $this->helper->sendEmailTemplate(
                $customer->getName(),
                $this->helper->getAdminEmailRecipients(),
                $this->helper->getAdminNotifyEmailTemplateId(),
                $this->helper->getSender(Data::TYPE_ADMIN),
                $templateData,
                $store->getId()
            );
        } catch(\Exception $e) {}

        return $this;
    }
}

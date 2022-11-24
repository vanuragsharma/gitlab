<?php

namespace Yalla\Apis\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Meetanshi\Mobilelogin\Model\ResourceModel\Mobilelogin\CollectionFactory;
use Twilio\Rest\ClientFactory as TwilioClientFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\CustomerFactory as createCustomer;

/**
 * Class ApiData
 * @package Yalla\Apis\Helper
 */
class ApiData extends AbstractHelper
{
    const MOBILELOGIN_ENABLED = 'mobilelogin/general/enabled';
    const OTP_LENGTH = 'mobilelogin/general/otplength';
    const OTP_TYPE = 'mobilelogin/general/otptype';
    const APIPROVIDER = 'mobilelogin/apisetting/apiprovider';
    const SENDER = 'mobilelogin/apisetting/senderid';
    const MESSAGETYPE = 'mobilelogin/apisetting/messagetype';
    const APIURL = 'mobilelogin/apisetting/apiurl';
    const APIKEY = 'mobilelogin/apisetting/apikey';
    const SID = 'mobilelogin/apisetting/sid';
    const TOKEN = 'mobilelogin/apisetting/token';
    const FROMMOBILENUMBER = 'mobilelogin/apisetting/frommobilenumber';

    const SMS_REGISTER = 'mobilelogin/otpsend/registrationmessage';
    const SMS_FORGOT = 'mobilelogin/otpsend/forgotmessage';
    const SMS_LOGIN = 'mobilelogin/otpsend/loginmessage';
    const SMS_UPDATE = 'mobilelogin/otpsend/updatemessage';

    const DEVELOPER_NUMBER = 'mobilelogin/developer/adminmobile';

    const BACK_IMAGE_URL = 'mobilelogin/layout/background';
    const BUTTON_COLOR = 'mobilelogin/layout/buttoncolor';
    const BUTTON_BG_COLOR = 'mobilelogin/layout/buttonbgcolor';
    const BORDER_COLOR_ONE = 'mobilelogin/layout/popupborderone';
    const BORDER_COLOR_TWO = 'mobilelogin/layout/popupbordertwo';

    const TITLE_LOGIN_WITH_OTP = 'mobilelogin/formsetting/login_otp';
    const TITLE_LOGIN_WITH_EMAIL = 'mobilelogin/formsetting/login_email';
    const TITLE_CREATE_ACCOUNT = 'mobilelogin/formsetting/register';
    const TITLE_FORGET_PASSWORD = 'mobilelogin/formsetting/forgot';

    const FLAG_ENABLED = 'mobilelogin/flag/enabled';
    const FLAG_COUNTRIES_ALLOWED = 'mobilelogin/flag/allow';
    const FLAG_PREFERED_COUNTRY = 'mobilelogin/flag/country_id';

    /**
     * @var
     */
    private $pageFactory;
    /**
     * @var CollectionFactory
     */
    protected $mobileloginFactory;
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var CustomerCollection
     */
    protected $customerFactory;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var ResultFactory
     */
    private $resultRedirect;
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var Customer
     */
    private $customer;
    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var DirectoryList
     */
    private $directory;
    /**
     * @var TwilioClientFactory
     */
    private $twilioClientFactory;
    /**
     * @var HttpContext
     */
    private $httpContext;
    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;
    /**
     * @var createCustomer
     */
    private $createCustomer;

    /**
     * ApiData constructor.
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param CollectionFactory $collectionFactory
     * @param CustomerCollection $customerFactory
     * @param Session $customerSession
     * @param ResultFactory $result
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param Customer $customer
     * @param UrlInterface $url
     * @param ManagerInterface $messageManager
     * @param DirectoryList $directoryList
     * @param TwilioClientFactory $twilioClientFactory
     * @param AccountManagementInterface $customerAccountManagement
     * @param HttpContext $httpContext
     * @param createCustomer $createCustomer
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CollectionFactory $collectionFactory,
        CustomerCollection $customerFactory,
        Session $customerSession,
        ResultFactory $result,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        Customer $customer,
        UrlInterface $url,
        ManagerInterface $messageManager,
        DirectoryList $directoryList,
        TwilioClientFactory $twilioClientFactory,
        AccountManagementInterface $customerAccountManagement,
        HttpContext $httpContext,
        createCustomer $createCustomer
    )
    {

        $this->jsonFactory = $jsonFactory;
        $this->mobileloginFactory = $collectionFactory;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->resultRedirect = $result;
        $this->storeManagerInterface = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->customer = $customer;
        $this->url = $url;
        $this->messageManager = $messageManager;
        $this->directory = $directoryList;
        $this->twilioClientFactory = $twilioClientFactory;
        $this->httpContext = $httpContext;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->createCustomer = $createCustomer;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getButtonColor()
    {
        if ($this->getConfig(self::BUTTON_COLOR) != '') {
            return '#' . $this->getConfig(self::BUTTON_COLOR);
        }
        return '#ffffff';
    }

    /**
     * @return string
     */
    public function getButtonBgColor()
    {
        if ($this->getConfig(self::BUTTON_BG_COLOR) != '') {
            return '#' . $this->getConfig(self::BUTTON_BG_COLOR);
        }
        return '#772a54';
    }

    /**
     * @return string
     */
    public function getBorderOne()
    {
        if ($this->getConfig(self::BORDER_COLOR_ONE) != '') {
            return '#' . $this->getConfig(self::BORDER_COLOR_ONE);
        }
        return '#772a54';
    }

    /**
     * @return string
     */
    public function getBorderTwo()
    {
        if ($this->getConfig(self::BORDER_COLOR_TWO) != '') {
            return '#' . $this->getConfig(self::BORDER_COLOR_TWO);
        }
        return '#170e3d';
    }

    /**
     * @return mixed
     */
    public function isFlagEnabled()
    {
        return $this->getConfig(self::FLAG_ENABLED);
    }

    /**
     * @return mixed
     */
    public function allowedCountries()
    {
        return $this->getConfig(self::FLAG_COUNTRIES_ALLOWED);
    }

    /**
     * @return mixed
     */
    public function preferedCountry()
    {
        return $this->getConfig(self::FLAG_PREFERED_COUNTRY);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getConfig($value)
    {
        return $this->scopeConfig->getValue($value, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function customerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * @return mixed
     */
    public function isMobileloginEnabled()
    {
        return $this->getConfig(self::MOBILELOGIN_ENABLED);
    }

    /**
     * @return mixed
     */
    public function getApiprovider()
    {
        return $this->getConfig(self::APIPROVIDER);
    }

    /**
     * @return mixed
     */
    public function getSid()
    {
        return $this->getConfig(self::SID);
    }

    /**
     * @return string
     */
    public function getCreateAccountLink()
    {
        return $this->_getUrl('customer/account/create');
    }

    /**
     * @return mixed|string
     */
    public function getOtpLoginTitle()
    {
        if ($this->getConfig(self::TITLE_LOGIN_WITH_OTP) != '') {
            return $this->getConfig(self::TITLE_LOGIN_WITH_OTP);
        } else {
            return 'Login With OTP';
        }
    }

    /**
     * @return mixed|string
     */
    public function getLoginWithEmailTitle()
    {
        if ($this->getConfig(self::TITLE_LOGIN_WITH_EMAIL) != '') {
            return $this->getConfig(self::TITLE_LOGIN_WITH_EMAIL);
        } else {
            return 'Login With Email/Mobile';
        }
    }

    /**
     * @return mixed|string
     */
    public function getCreateAccountTitle()
    {
        if ($this->getConfig(self::TITLE_CREATE_ACCOUNT) != '') {
            return $this->getConfig(self::TITLE_CREATE_ACCOUNT);
        } else {
            return 'Create Account';
        }
    }

    /**
     * @return mixed|string
     */
    public function getForgetTitle()
    {
        if ($this->getConfig(self::TITLE_FORGET_PASSWORD) != '') {
            return $this->getConfig(self::TITLE_FORGET_PASSWORD);
        } else {
            return 'Forgot Password';
        }
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImageUrl()
    {
        $backUrl = $this->getConfig(self::BACK_IMAGE_URL);
        $mediaUrl = $this->storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $backImageUrl = $mediaUrl . 'mobilelogin/backimage/' . $backUrl;
        return $backImageUrl;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->getConfig(self::TOKEN);
    }

    /**
     * @return mixed
     */
    public function getAdminmobile()
    {
        return $this->getConfig(self::FROMMOBILENUMBER);
    }

    /**
     * @return mixed
     */
    public function getCustomerMobile()
    {
        return $this->customerSession->getCustomer()->getMobileNumber();
    }

    /**
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function getCustomerCollection()
    {
        return $this->customerFactory->create();
    }

    /**
     * @param $id
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadCustomerById($id)
    {
        $customer = $this->customerRepository->getById($id);
        return $customer;
    }

    /**
     * @return mixed
     */
    public function getStoreName()
    {
        return $this->getConfig('general/store_information/name');
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsiteId()
    {
        return $this->storeManagerInterface->getStore()->getWebsiteId();
    }

    /**
     * @param $mobile
     * @return \Magento\Framework\DataObject
     */
    public function getCustomerCollectionMobile($mobile)
    {
        try {
            $customer = $this->customerFactory->create()
                ->addAttributeToSelect("*")
                ->addAttributeToFilter("mobile_number", ["eq" => $mobile])
                ->getFirstItem();
            return $customer;
        } catch (\Exception $e) {
            $this->_logger->info("Error" . $e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function isEnable()
    {
        return $this->scopeConfig->getValue(self::MOBILELOGIN_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $data
     * @return array
     */
    public function createPost($data)
    {
        $response = [
            'success' => "false",
            'errormsg' => "Something went wrong, please try again after sometime.",
            'successmsg' => "",
            'customurl' => $this->url->getUrl('customer/account/')
        ];

        try {


            if ($this->customerLoggedIn()) {
                $response['successmsg'] = "You're already logged in.";
                $response['success'] = "true";

            } else {

                $firstName = $data['firstname'];
                $lastName = $data['lastname'];
                $email = $data['email'];
                $password = $data['password'];
                $mobilenumber = $data['mobile'];
                $prefix = $data['prefix'];
                $newsletter_subscription = $data['newsletter_subscription'];

                $websiteId = $this->storeManagerInterface->getWebsite()->getWebsiteId();

                $customer = $this->createCustomer->create();
                $customer->setWebsiteId($websiteId);

                if ($customer->loadByEmail($email)->getId()) {
                    $response['errormsg'] = ' There is already an account registered with this  ' . $email;
                } else {

                    $username = $this->getCustomerCollectionMobile($mobilenumber);

                    if ($username->getId()) {

                        $response['errormsg'] = $mobilenumber . " = This mobile number is already registered.";

                    } else {

                        try {

                            $customer->setEmail($email);
                            $customer->setFirstname($firstName);
                            $customer->setLastname($lastName);
                            $customer->setPassword($password);
                            $customer->setMobileNumber($mobilenumber);
                            $customer->setPrefix($prefix);
                            $customer->setForceConfirmed(true);
                            $customer->save();

                            //$this->customerSession->setCustomerAsLoggedIn($customer);

                            //$customer->sendNewAccountEmail();
                            
                            if($newsletter_subscription){
                                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                $subscriber= $objectManager->create('Magento\Newsletter\Model\SubscriberFactory'); 
                                $subscriber->create()->subscribe($email);
                            }
                            $customerData = $this->customerRepository->getById($customer->getId());
		            $response['customer'] = $this->getCustomerData($customerData);
                            $response['successmsg'] = "Customer account with $email has been created successfully.";
                            $response['success'] = "true";

                        } catch (\Exception $e) {
                            $response['errormsg'] = $e->getMessage();
                        }
                    }


                }
            }
        } catch (\Exception $e) {
            $response['errormsg'] = $e->getMessage();
        }

        return $response;
    }


    /**
     * @param $data
     * @return array
     */
    public function loginPost($data)
    {
        $response = [
            'success' => "false",
            'errormsg' => "Something went wrong, please try again after sometime.",
            'successmsg' => "",
            'customurl' => $this->url->getUrl('customer/account/')
        ];

        if ($this->customerLoggedIn()) {
            $response['successmsg'] = "You're already logged in.";
            $response['success'] = "true";

        } else {

            $username = trim($data['emailmobile']);
            $password = $data['password'];

            if (is_numeric($username)) {
                $username = $this->getCustomerCollectionMobile($username)->getEmail();
            } else if (substr($username, 0, 1) == "+") {
                $username = $this->getCustomerCollectionMobile($username)->getEmail();
            }

            try {
                $customer = $this->customerAccountManagement->authenticate($username, $password);
                
                //$this->customerSession->setCustomerDataAsLoggedIn($customer);
                //$this->customerSession->regenerateId();
                $response['successmsg'] = 'Login successful.';
                $response['errormsg'] = '';
                $response['success'] = "true";
                $response['customer'] = $this->getCustomerData($customer);
            } catch (\Exception $e) {
                $response['errormsg'] = 'Invalid username or password.';
            }
        }
        return $response;
    }

    /**
     * @param $data
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function otpVerify($data)
    {
        $otptype = $data['otptype'];
        $mobilenumber = $data['mobilenumber'];
        $verifyCode = $data['otpcode'];
        $oldMobilenumer = "";
        if (isset($data['oldmobile'])) {
            $oldMobilenumer = $data['oldmobile'];
        }

        $response = [
            'success' => "false",
            'errormsg' => "Invalid OTP.",
            'successmsg' => "",
            'customurl' => ""
        ];
        $resultPage = $this->mobileloginFactory->create()
            ->addFieldToFilter('mobilenumber', ['eq' => $mobilenumber])
            ->getFirstItem();
        if ($resultPage->getId()) {
            if ($otptype == "register") {
                if ($resultPage->getRegisterOtp() == $verifyCode) {
                    $resultPage->setRegisterVerify(1);
                    $resultPage->save();
                    $response['success'] = "true";
                    $response['successmsg'] = "OTP verified successfully.";
                }
            }
            if ($otptype == "forgot") {
                if ($resultPage->getForgotOtp() == $verifyCode) {
                    $resultPage->setForgotVerify(1);
                    $resultPage->save();
                    $response['success'] = "true";
                    $response['successmsg'] = "OTP verified successfully.";
                }
            }
            if ($otptype == "login") {
                if ($resultPage->getLoginOtp() == $verifyCode) {
                    $resultPage->setLoginVerify(1);
                    $resultPage->save();
                    $this->customer->setWebsiteId($this->getWebsiteId());
                    $customerData = $this->customerRepository->getById($this->getCustomerCollectionMobile($mobilenumber)->getId());
                    //$this->customerSession->setCustomerAsLoggedIn($customerData);
                    $response['customurl'] = $this->url->getUrl('customer/account/');
                    $response['successmsg'] = "OTP verified successfully.";
                    $response['success'] = "true";
                    $response['customer'] = $this->getCustomerData($customerData);
                }
            }
            if ($otptype == "update") {
                if ($resultPage->getUpdateOtp() == $verifyCode) {
                    $resultPage->setUpdateVerify(1);
                    $resultPage->setMobilenumber($mobilenumber);
                    $resultPage->save();
                    $customer = $this->loadCustomerById($this->customerSession->getCustomer()->getEntityId());
                    $customer->setCustomAttribute('mobile_number', $mobilenumber);
                    $this->customerRepository->save($customer);
                    if ($oldMobilenumer != "") {
                        $this->messageManager->addSuccessMessage("Mobile Updated from " . $oldMobilenumer . " to " . $mobilenumber . " Succeessfully");
                    } else {
                        $this->messageManager->addSuccessMessage("Mobile number has been updated successfully.");
                    }
                    $response['success'] = "true";
                    $response['successmsg'] = "OTP verified successfully.";
                }
            }
        }
        return $response;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreUrl()
    {
        $this->storeManagerInterface->getStore()->getBaseUrl();
    }

    /**
     * @param $data
     * @return array
     */
    public function otpSave($data)
    {
        try {
            $response = [
                'success' => "true",
                'errormsg' => "",
                'successmsg' => ""
            ];
            if (isset($data['otptype']) and isset($data['mobilenumber'])) {
                $otptype = $data['otptype'];
                $mobilenumber = $data['mobilenumber'];
                $resendotp = $data['resendotp'];
                $oldMobilenumber = $data['oldmobile'];
                $otpcode = $this->generateOtpCode();

                if ($otptype == "register") {
                    
                    $email = $data['email'];
                    $websiteId = $this->storeManagerInterface->getWebsite()->getWebsiteId();
                    $customer = $this->createCustomer->create();
                    $customer->setWebsiteId($websiteId);

                    if ($customer->loadByEmail($email)->getId()) {
                        $response['success'] = 'false';
                        $response['errormsg'] = 'There is already an account registered with this  ' . $email;
                    } else if ($this->getCustomerCollectionMobile($mobilenumber)->getId()) {
                        $response['success'] = 'false';
                        $response['errormsg'] = 'This mobile number is already registered.';
                    } else {
                        $resultPage = $this->mobileloginFactory->create()
                            ->addFieldToFilter('mobilenumber', ['eq' => $mobilenumber])
                            ->getFirstItem();
                        if (!($resultPage->getId())) {
                            $resultPage->setMobilenumber($mobilenumber);
                        }
                        $resultPage->setRegisterOtp($otpcode)
                            ->setRegisterVerify(0)
                            ->save();
                        $response['successmsg'] = "New OTP has been sent to your mobile number  " . $mobilenumber;
                        $message = $this->getMessageText($otpcode, $otptype);
                        //if ($this->curlApi($mobilenumber, $message)) {
                        //    if(!$resendotp){
                                $response['successmsg'] = "OTP has been sent on your mobile number " . $mobilenumber;
                        //    }
//                        } else {
//                            $response['success'] = 'false';
//                            $response['errormsg'] = 'Message Send Failed ';
//                        }
                          $response['otp'] = $otpcode;
                    }
                } elseif ($otptype == "forgot") {
                    if (!($this->getCustomerCollectionMobile($mobilenumber)->getId())) {
                        $response['success'] = 'false';
                        $response['errormsg'] = 'This mobile number is not registered.';
                    } else {
                        $resultPage = $this->mobileloginFactory->create()
                            ->addFieldToFilter('mobilenumber', ['eq' => $mobilenumber])
                            ->getFirstItem();

                        if ($resultPage->getId()) {
                            $resultPage->setForgotOtp($otpcode)
                                ->setForgotVerify(0)
                                ->save();
                        } else {
                            $response['success'] = 'false';
                            $response['errormsg'] = 'Mobile number not found.';
                        }
                        $message = $this->getMessageText($otpcode, $otptype);
                        if ($this->curlApi($mobilenumber, $message)) {
                            $response['successmsg'] = "OTP has been sent on your mobile number " . $mobilenumber;
                        } else {
                            $response['success'] = 'false';
                            $response['errormsg'] = 'Message Send Failed ';
                        }
                    }
                } elseif ($otptype == "update") {
                    if ($this->getCustomerCollectionMobile($mobilenumber)->getId()) {
                        $response['success'] = 'false';
                        $response['errormsg'] = 'This mobile number is already registered.';
                    } else {
                        $resultPage = $this->mobileloginFactory->create()
                            ->addFieldToFilter('mobilenumber', ['eq' => $mobilenumber])
                            ->getFirstItem();

                        if ($resultPage->getId()) {
                            $resultPage->setUpdateOtp($otpcode)
                                ->setUpdateVerify(0)
                                ->save();
                        } else {
                            $resultPage->setMobilenumber($mobilenumber)
                                ->setUpdateOtp($otpcode)
                                ->setUpdateVerify(0)
                                ->save();
                        }
                        $response['successmsg'] = "OTP has been sent on your mobile number " . $mobilenumber;
                        $message = $this->getMessageText($otpcode, $otptype);
                        if ($this->curlApi($mobilenumber, $message)) {
                            $response['successmsg'] = "OTP has been sent on your mobile number " . $mobilenumber;
                        } else {
                            $response['success'] = 'false';
                            $response['errormsg'] = 'Message Send Failed ';
                        }
                    }
                } elseif ($otptype == "login") {
                    if (!($this->getCustomerCollectionMobile($mobilenumber)->getId())) {
                        $response['success'] = 'false';
                        $response['errormsg'] = 'This mobile number is not registered.';
                    } else {
                        $resultPage = $this->mobileloginFactory->create()
                            ->addFieldToFilter('mobilenumber', ['eq' => $mobilenumber])
                            ->getFirstItem();

                        if ($resultPage->getId()) {
                            $resultPage->setLoginOtp($otpcode)
                                ->setLoginVerify(0)
                                ->save();
                        } else {
                            $resultPage->setMobilenumber($mobilenumber)
                                ->setLoginOtp($otpcode)
                                ->setLoginVerify(0)
                                ->save();
                        }
                        $response['successmsg'] = "OTP has been sent on your mobile number " . $mobilenumber;
                        $message = $this->getMessageText($otpcode, $otptype);
//                        if ($this->curlApi($mobilenumber, $message)) {
//                            $response['successmsg'] = "OTP has been sent on your mobile number " . $mobilenumber;
//                        } else {
//                            $response['success'] = 'false';
//                            $response['errormsg'] = 'Message Send Failed ';
//                        }
                        $response['successmsg'] = "OTP has been sent on your mobile number " . $mobilenumber;
                        $response['otp'] = $otpcode;
                    }
                }

                return $response;
            }
        } catch (\Exception $e) {
            $this->_logger->info("Otp Save Error" . $e->getMessage());
            return $response;
        }
    }

    /**
     * @return mixed
     */
    public function getOtpLength()
    {
        $length = $this->getConfig(self::OTP_LENGTH);
        return $length;
    }

    /**
     * @return false|string
     */
    public function generateOtpCode()
    {
        $length = $this->getOtpLength();
        $otptype = $this->getConfig(self::OTP_TYPE);
        if ($otptype == 1) {
            $randomString = substr(str_shuffle("0123456789"), 0, $length);
        } elseif ($otptype == 2) {
            $randomString = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
        } else {
            $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
        }
        return $randomString;
    }

    /**
     * @param $otpcode
     * @param $otptype
     * @return string|string[]
     */
    public function getMessageText($otpcode, $otptype)
    {
        try {
            $storename = $this->getStoreName();
            $storeUrl = $this->storeManagerInterface->getStore()->getBaseUrl();
            $message = "";
            if ($otptype == "register") {
                $message = $this->getConfig(self::SMS_REGISTER);
            } elseif ($otptype == "forgot") {
                $message = $this->getConfig(self::SMS_FORGOT);
            } elseif ($otptype == "login") {
                $message = $this->getConfig(self::SMS_LOGIN);
            } elseif ($otptype == "update") {
                $message = $this->getConfig(self::SMS_UPDATE);
            }

            $replaceArray = [$otpcode, $storename, $storeUrl];
            $originalArray = ['{{otp_code}}', '{{shop_name}}', '{{shop_url}}'];
            $newMessage = str_replace($originalArray, $replaceArray, $message);
            return $newMessage;
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function sendDeveloperSms()
    {
        $adminMobile = $this->getConfig(self::DEVELOPER_NUMBER);
        if ($this->curlApi($adminMobile, 'Testing Api check')) {
            return 'SMS send';
        } else {
            return 'Error in SMS send';
        }
    }

    /**
     * @return string
     */
    public function synchronize()
    {
        $customerCollection = $this->customerFactory->create();
        foreach ($customerCollection as $customer) {
            if ($customer->getMobileNumber() == '') {
                $mobileNumber = '';
                foreach ($customer->getAddresses() as $address) {
                    if ($address->getTelephone() != '') {
                        $mobileNumber = $address->getTelephone();
                        break;
                    }
                }
                $customer->setMobileNumber($mobileNumber);
                $customer->save();
            }
        }
        return 'Success';
    }

    /**
     * @param $mobilenumber
     * @param $message
     * @return bool|string
     */
    public function curlApi($mobilenumber, $message)
    {
        try {
            if ($this->isEnable()) {
                if ($this->getApiprovider() == "msg91") {
                    $msg = urlencode($message);
                    $apikey = $this->getConfig(self::APIKEY);
                    $senderid = $this->getConfig(self::SENDER);
                    $url = $this->getConfig(self::APIURL);
                    $msgtype = $this->getConfig(self::MESSAGETYPE);

                    $postUrl = $url . "?sender=" . $senderid . "&route=" . $msgtype . "&mobiles=" . $mobilenumber . "&authkey=" . $apikey . "&message=" . $msg . "";
                    $curl = curl_init();
                    curl_setopt_array(
                        $curl,
                        [
                            CURLOPT_URL => $postUrl,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "GET",
                            CURLOPT_SSL_VERIFYHOST => 0,
                            CURLOPT_SSL_VERIFYPEER => 0,
                        ]
                    );
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                    if ($err) {
                        return "cURL Error #:" . $err;
                    } else {
                        if ($response) {
                            return true;
                        }
                    }
                } elseif ($this->getApiprovider() == "textlocal") {
                    $url = $this->getConfig(self::APIURL);
                    $apiKey = urlencode($this->getConfig(self::APIKEY));
                    $numbers = [$mobilenumber];
                    $sender = urlencode($this->getConfig(self::SENDER));
                    $message = rawurlencode($message);
                    $numbers = implode(',', $numbers);
                    $data = ['apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    curl_close($ch);

                    $responseArray = json_decode($response, true);
                    if ($responseArray['status'] == "success") {
                        return true;
                    } else {
                        return false;
                    }
                } elseif ($this->getApiprovider() == "twilio") {
                    $sid = $this->getConfig(self::SID);
                    $token = $this->getConfig(self::TOKEN);
                    $fromMobile = $this->getConfig(self::FROMMOBILENUMBER);
                    $twilio = $this->twilioClientFactory->create([
                        'username' => $sid,
                        'password' => $token
                    ]);

                    $message = $twilio->messages
                        ->create(
                            $mobilenumber,
                            [
                                "body" => $message,
                                "from" => $fromMobile
                            ]
                        );
                    if ($message->sid) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage("Message Send Error" . $e->getMessage());
        }
    }
    
    private function getCustomerData($customer) {
        $customerData = array(
            'id' => $customer->getId(),
            'created_at' => $customer->getCreatedAt(),
            'updated_at' => $customer->getUpdatedAt(),
            'email' => $customer->getEmail(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'store_id' => $customer->getId(),
            'prefix' => $customer->getPrefix(),
            'mobile' => $customer->getCustomAttribute('mobile_number')->getValue()
        );
        
        return $customerData;
    }
}

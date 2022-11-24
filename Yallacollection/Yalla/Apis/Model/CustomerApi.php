<?php

namespace Yalla\Apis\Model;

use Yalla\Apis\Api\CustomerApiInterface;
use Yalla\Apis\Model\Customer\Model\CustomerFactory;
use Yalla\Apis\Model\Data\Customer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Math\Random;
use Magento\Customer\Api\AccountManagementInterface;
use Yalla\Apis\Model\Customer\Model\CustomerRegistry;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Yalla\Apis\Helper\ApiData;
use Magento\Customer\Model\CustomerFactory as createCustomer;


class CustomerApi implements CustomerApiInterface {

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerData;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Customer Account Service
     *
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var request
     */
    private $request;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;
    /**
     * @var ApiData
     */
    protected $apiHelper;
    /**
     * @var createCustomer
     */
    private $createCustomer;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     * @param Http $request
     * @param Encryptor $encryptor
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterface $customerData
     * @param Random $mathRandom
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRegistry $customerRegistry
     * @param DateTimeFactory|null $dateTimeFactory
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            CustomerFactory $customerFactory,
            \Magento\Framework\App\Request\Http $request,
            Encryptor $encryptor,
            CustomerRepositoryInterface $customerRepository,
            CustomerInterface $customerData,
            Random $mathRandom,
            AccountManagementInterface $accountManagement,
            CustomerRegistry $customerRegistry,
            DateTimeFactory $dateTimeFactory = null,
            TokenModelFactory $tokenFactory,
	    ApiData $apiHelper,
	    createCustomer $createCustomer
    ) {
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->request = $request;
        $this->encryptor = $encryptor;
        $this->customerRepository = $customerRepository;
        $this->customerData = $customerData;
        $this->mathRandom = $mathRandom;
        $this->accountManagement = $accountManagement;
        $this->customerRegistry = $customerRegistry;
        $this->dateTimeFactory = $dateTimeFactory ?: ObjectManager::getInstance()->get(DateTimeFactory::class);
        $this->tokenFactory = $tokenFactory;
	$this->apiHelper = $apiHelper;
        $this->createCustomer = $createCustomer;
    }

    /**
     * Return Added customer
     * @return array
     *
     */
    public function socialregister() {
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
        $postData = file_get_contents("php://input");
        if (!empty($postData)) {
            $request = json_decode($postData, true);
            $customerEmail = isset($request['email']) ? $request['email'] : '';
            $mobile = isset($request['mobile']) ? $request['mobile'] : '';

	    $password = "Pass@".rand() . time();
            $firstname = $request['firstname'];
            $lastname = $request['lastname'];
            
	    if(empty($firstname)){
		$firstname = "Guest";
	    }
	    if(empty($lastname)){
		$lastname = "User";
	    }

	    if (empty($customerEmail) && empty($mobile)) {
                $response[] = ['status' => 405, 'msg' => 'Mandatory parameters are missing!'];
                return $response;
            }
            if (!isset($request['social_login']) || empty($request['social_login'])) {
                $response[] = ['status' => 405, 'msg' => 'Invalid request!'];
                return $response;
            }

            // Check if customer already exist
           
            $customerModel = $objectManager->create('Magento\Customer\Model\Customer');
            $customerModel->setWebsiteId(1);
	    
	    if(!empty($customerEmail)){
		$customerModel = $customerModel->loadByEmail($customerEmail);
	    }

	    if(empty($customerEmail) && !empty($mobile)){
		$customerModel = $this->apiHelper->getCustomerCollectionMobile($mobile);
	    }

            if ($customerModel->getId()) {

                $customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
                $customerFactory = $customerRepository->getById($customerModel->getId());

                $customerFactory->setCustomAttribute('social_login', $request['social_login']);

                $customer = $customerRepository->save($customerFactory);

                $customerAttributeData = $customer->__toArray();

		$customer_mobile = $customerFactory->getCustomAttribute('mobile_number');
		$mobile_number = '';
		if($customer_mobile){
			$mobile_number = $customer_mobile->getValue();
		}
                $data = [
                    'id' => $customerFactory->getId(),
                    'created_at' => $customerFactory->getCreatedAt(),
                    'updated_at' => $customerFactory->getUpdatedAt(),
                    'email' => $customerFactory->getEmail(),
                    'firstname' => $customerFactory->getFirstname(),
                    'lastname' => $customerFactory->getLastname(),
                    'store_id' => $customerFactory->getId(),
                    'prefix' => $customerFactory->getPrefix(),
                    'mobile' => $mobile_number
                ];


                $response = ['success' => 'true', 'msg' => 'Customer successfully logged in', 'collection' => $data];
                echo json_encode($response);
		exit;
            }

            $prefix = $request['prefix'];

            if ($password !== null) {
                $hash = $this->createPasswordHash($password);
            } else {
                $hash = null;
            }

            $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
            $StoreId = $this->storeManager->getStore()->getId();

            $customer = $this->createCustomer->create();

            //$customer   = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId);
            $customer->setStoreId($StoreId);

            if ($customer->getId() === null) {
                $storeName = $this->storeManager->getStore($customer->getStoreId())->getName();
                $customer->setCreatedIn($storeName);
            }

            // Preparing data for new customer
            $customer->setEmail($customerEmail);
            $customer->setFirstname($firstname);
            $customer->setLastname($lastname);
	    $customer->setPassword($password);
            $customer->setPrefix($prefix);
	    $customer->setMobileNumber($mobile);

            $customer->setCustomAttribute('social_login', $request['social_login']);
	    $customer->setForceConfirmed(true);

            // Save data
            try {
                // If customer exists existing hash will be used by Repository
                $customer->save();

                //$customer = $this->accountManagement->createAccount($customer, $password);
            } catch (AlreadyExistsException $e) {
                $response = ['success' => 'false', 'msg' => 'Customer with same email already exist.'];
                echo json_encode($response);
		exit;
            } catch (\Exception $e) {
                $response = ['success' => 'false', 'msg' => $e->getMessage(),];
                echo json_encode($response);
		exit;
            }

            $data = $this->getCustomer($customer->getId());
            $response = ['success' => 'true', 'msg' => 'Customer successfully registered', 'collection' => $data];
            echo json_encode($response);
	    exit;
        } else {
            $response = ['success' => 'false', 'msg' => 'There was an error processing the request. Please try again later.'];
            echo json_encode($response);
	    exit;
        }
    }

    /**
     * Return Updated Customer.
     * @return array
     *
     */
    public function editProfile() {
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
        $postData = file_get_contents("php://input");
        if (!empty($postData)) {
            $request = json_decode($postData, true);

            
            $customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');

            if (!isset($request['customer_id'])) {
                $response = ['success' => 'false', 'msg' => 'Invalid Request!!'];
                return $response;
            }
	    try {
                $customerFactory = $customerRepository->getById($request['customer_id']);
            } catch (\Exception $e) {
                $response = ['success' => 'false', 'msg' => 'Customer does not exist!'];
                echo json_encode($response);
		exit;
            }

            if (empty($customerFactory)) {
                $response[] = ['success' => 'false', 'msg' => 'Customer not found!!'];
                echo json_encode($response);
		exit;
            }

            if (!empty($request['firstname'])) {
                $customerFactory->setFirstname($request['firstname']);
            }

            if (!empty($request['lastname'])) {
                $customerFactory->setLastname($request['lastname']);
            }

            if (!empty($request['prefix'])) {
                $customerFactory->setPrefix($request['prefix']);
            }

            if (!empty($request['email'])) {
                $customerFactory->setEmail($request['email']);
            }

            $customerFactory->setUpdatedAt(date('Y-m-d H:i:s'));

            try {
                $customer = $customerRepository->save($customerFactory);
                if (isset($request['is_subscribed']) && !empty($request['is_subscribed'])) {
                    $subscriber= $objectManager->create('Magento\Newsletter\Model\Subscriber');
                    $checkSubscriber = $subscriber->loadByCustomerId($request['customer_id']);
                    
                    if (!$checkSubscriber->isSubscribed()) {
                        $subscriber= $objectManager->create('Magento\Newsletter\Model\SubscriberFactory'); 
                        $subscriber->create()->subscribe($customerFactory->getEmail());
                    }
                }
            } catch (AlreadyExistsException $e) {
                $response = ['success' => 'false', 'msg' => 'Customer with same email already exist.'];
                echo json_encode($response);
		exit;
            } catch (\Exception $e) {
                $response = ['success' => 'false', 'msg' => $e->getMessage(),];
                echo json_encode($response);
		exit;
            }

            if (!empty($request['current_password']) && isset($request['new_password']) && !empty($request['new_password'])) {
                $accountManagement = $objectManager->create('Magento\Customer\Model\AccountManagement');
                try {
                    $accountManagement->changePassword($customerFactory->getEmail(), $request['current_password'], $request['new_password']);
                } catch (InvalidEmailOrPasswordException $e) {
                    $response = ['success' => 'false', 'msg' => 'The current password doesnot match this account.',];
		    echo json_encode($response);
		    exit;
                } catch (\Exception $e) {
                    $response = ['success' => 'false', 'msg' => $e->getMessage(),];
                    echo json_encode($response);
		    exit;
                }
            }

            $customerAttributeData = $customer->__toArray();

            $dob = $customerFactory->getDob();
            $dob = explode(' ', $dob);

            $data = [
                'id' => $customerFactory->getId(),
                'created_at' => $customerFactory->getCreatedAt(),
                'email' => $customerFactory->getEmail(),
                'firstname' => $customerFactory->getFirstname(),
                'lastname' => $customerFactory->getLastname(),
                'store_id' => $customerFactory->getId(),
                'prefix' => $customerFactory->getPrefix()
            ];

            $response = ['success' => 'true', 'msg' => 'Customer profile successfully updated.', 'collection' => $data];
            echo json_encode($response);
	    exit;
        } else {
            $response = ['success' => 'false', 'msg' => 'There was an error processing the request. Please try again later.'];
            echo json_encode($response);
	    exit;
        }
    }

    /**
     * Create a hash for the given password
     *
     * @param string $password
     * @return string
     */
    protected function createPasswordHash($password) {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * Create a hash for the given password
     *
     * @param int $customerId
     * @return array
     */
    protected function getCustomer($customerId) {
        $model = $this->customerRepository->getById($customerId);

        $customerAttributeData = $model->__toArray();

        $customer_mobile = $model->getCustomAttribute('mobile_number');
	$mobile_number = '';
	if($customer_mobile){
		$mobile_number = $customer_mobile->getValue();
	}

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $subscriber= $objectManager->create('Magento\Newsletter\Model\Subscriber');
        $checkSubscriber = $subscriber->loadByCustomerId($customerId);
        $isSubscribed = 0;
        if ($checkSubscriber->isSubscribed()) {
            $isSubscribed = 1;
        }

        return [
            'id' => $model->getId(),
            'created_at' => $model->getCreatedAt(),
            'updated_at' => $model->getUpdatedAt(),
            'email' => $model->getEmail(),
            'firstname' => $model->getFirstname(),
            'lastname' => $model->getLastname(),
            'store_id' => $model->getId(),
            'prefix' => $model->getPrefix(),
            'mobile' => $mobile_number,
            'is_subscribed' => $isSubscribed
        ];
    }

    /**
     * Get customer details
     *
     * @param int $customerId
     * @return array
     */
    public function getProfile() {
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}  
        $post = file_get_contents("php://input");
        if (!empty($post)) {
            $request = json_decode($post, true);
        }

        $response = ['success' => 'true', 'msg' => __('Success'), 'collection' => array()];

        if (!isset($request['customer_id']) || !isset($request['token'])) {
            echo json_encode(['status' => 'error', 'msg' => __('Mandatory parameters are missing.')]);
            exit;
        }

        if (!isset($request['customer_id']) || !isset($request['token'])) {
            echo json_encode(['status' => 'error', 'msg' => __('Mandatory parameters are missing.')]);
            exit;
        }

        $token = "6REF$&@#J7USKkfd*#L2";
        if ($token != $request['token']) {
            echo json_encode(['status' => 'error', 'msg' => __('Invalid request.')]);
            exit;
        }
        $customerId = $request['customer_id'];

        try {

            $customer = $this->getCustomer($customerId);
            $response['collection'] = $customer;
            echo json_encode($response);
            exit;
        } catch (\Exception $ex) {
            echo json_encode(['success' => 'false', 'msg' => $ex->getMessage()]);
            exit;
        }
    }

}

<?php

namespace Yalla\Apis\Model;

use Yalla\Apis\Api\AddressManagementInterface;

class AddressManagement implements AddressManagementInterface
{

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Customer\Model\Address
     */
    protected $address;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * userhelper
     */
    protected $userhelper;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $regionCollection;

	protected $request;

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Customer\Model\Address $address
     * @param \Magento\User\Helper\Data $userhelper
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Address $address,
        \Magento\User\Helper\Data $userhelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection,
	\Magento\Framework\App\Request\Http $request,
        array $data = []
    ){
        $this->customer = $customer;
        $this->address = $address;
        $this->userhelper=$userhelper;
        $this->quoteFactory = $quoteFactory;
        $this->addressRepository = $addressRepository;
        $this->regionCollection = $regionCollection;
	$this->request = $request;
    }


    /**
     * Set response data
     */
    protected function _setResponseData($data)
    {
        $response=array();
        $response[] = $data;
        return $response;
    }

    /**
     * Get customer by id
     */
    public function getCustomerById($customerId){
        return $this->customer->load($customerId);
    }

    /**
     * Get customer address by id
     */
    public function getCustomerAddressById($addressId)
    {
        return $this->address->load($addressId);
    }

    /**
     * Get customer addresses by id
     */
    public function getCustomerAddresses($customerId)
    {
        $addressData=array();
        $customer=$this->getCustomerById($customerId);
        $addresses=$customer->getAddresses();
        $defaultShipping=false;

        //get default shipping address
        $shippingAddressId = $customer->getDefaultShipping();
	if($shippingAddressId){
            $shippingAddress=$this->getCustomerAddressById($shippingAddressId);
            if($shippingAddress->getId()){
                $defaultShipping=true;
            }
        }
        foreach ($addresses as $address) {
            if($defaultShipping && $address->getId()==$shippingAddressId){
                continue;
            }else{
                $addressData[$address->getId()]=$this->getAddressData($address);
            }
        }

	krsort($addressData);
        
	if(isset($shippingAddress) && $shippingAddress->getId()){
		array_unshift($addressData,$this->getAddressData($shippingAddress)); // Set default address on first index
        }

        return array_values($addressData);
    }

    /**
     * Retrieve customer address.
     * @param string $customer_id
     * @return array
     */
    public function retrieveAddress($customer_id)
    {
        $post = file_get_contents("php://input");

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}
        if (!empty($post)) {
            $request = json_decode($post, true);
        }

	$response = ['status' => 'success', 'msg' => __('success'), 'collection' => array()];

	if(empty($customer_id)){
		$response['status'] = "success";
		$response['msg'] = __("Invalid customer.");
                $response['collection'] = [];
		echo json_encode($response);
		exit;
	}

	
	//Multi store view
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
		$apiHelper = $objectManager->get('Yalla\Apis\Helper\Data');
        $apiHelper->setStore($lang, $country);
        //Multi store view

        $customer=$this->getCustomerById($customer_id);
        if($customer->getId()){
            $addresses = $this->getCustomerAddresses($customer_id);
            if($addresses){
                $response['status'] = "success";
                $response['collection'] = $addresses;
            }
            else{
                $response['status'] = "error";
                $response['msg'] = __("No Address found");
            }
        }else{
            $response['status'] = 'error';
            $response['msg'] = __("Invalid Customer Id");
        }

	echo json_encode($response);
	exit;
    }

    /**
     * Delete customer address.
     * @param string $address_id
     * @return array
     */
    public function deleteAddress($address_id)
    {
        $message=array();

	$response = ['status' => 'success', 'msg' => 'success'];

        $address=$this->getCustomerAddressById($address_id);
        $customerId=$address->getParentId();

        try{
            $this->addressRepository->deleteById($address_id);
            $response['msg'] = __("Customer address deleted successfully");
            $response['collection'] = $this->getCustomerAddresses($customerId);
        } catch(\Exception $e) {
            $response['status'] = 'error';
            $response['msg'] = $e->getMessage();
        }

	echo json_encode($response);
	exit;
    }

    /**
     * Set response data
     */
    public function getAddressFields()
    {
        return $address=array(
            'id',
	    'alias',
            'firstname',
            'lastname',
            'country_id',
            'region_id',
            'region',
            'city',
            'street',
            'address_line_1',
            'address_line_2',
            'telephone',
            'postcode',
            'company',
	    'building_number',
	    'zone',
	    'landmark',
            'is_default_billing',
            'is_default_shipping'
        );
    }

    /**
     * Get Address Data
     */
    public function getAddressData($address)
    {
        $addressData=array();

        if($address->getId()){
            $requiredFields=$this->getAddressFields();
            foreach ($requiredFields as $field) {
                switch ($field) {
                    case 'id':
                        $addressData['address_id']=$address->getId();
                        break;
                    case 'street':
                        $street=$address->getStreet();
                        $addressData['street']=isset($street[0])?$street[0]:"";
                    case 'address_line_1':
                        $street=$address->getStreet();
                        $addressData['address_line_1']=isset($street[1])?$street[1]:"";
                        break;
                    case 'address_line_2':
                        $street=$address->getStreet();
                        $addressData['address_line_2']=isset($street[2])?$street[2]:"";
                        break;
                    case 'is_default_billing':
                        $addressData['is_default_billing']=(int)$this->_isDefaultBillingAddress($address);
                        break;

                    case 'is_default_shipping':
                        $addressData['is_default_shipping']=(int)$this->_isDefaultShippingAddress($address);
                        break;
                    case 'building_number':
                    	$street=$address->getStreet();
                        $addressData['building_number']=isset($street[1])?$street[1]:"";
                        break;
                    default:
                        $addressField= $address->getData($field);
			$addressData[$field]=!empty($addressField)?$addressField:"";
                        break;
                }
            }
        }

	$customer_address_id = $address->getData('customer_address_id');
	if($customer_address_id){
		// get custom attributes
		$address = $this->addressRepository->getById($customer_address_id);
		$attributes = $address->getCustomAttributes();
		foreach($attributes as $attribute)
		{
			$addressData[$attribute->getAttributeCode()]=!empty($attribute->getValue())?$attribute->getValue():"";
		}
	}
        return $addressData;
    }

    public function _isDefaultBillingAddress($address)
    {
        $isDefault=false;
	if($address->getCustomer()){
		$customerId=$address->getCustomer()->getId();
		$customer=$this->getCustomerById($customerId);
		if($customer->getDefaultBilling() == $address->getId()){
		    $isDefault=true;
		}
	}
        return $isDefault;
    }

    public function _isDefaultShippingAddress($address)
    {
        $isDefault=false;
	if($address->getCustomer()){
		$customerId=$address->getCustomer()->getId();
		$customer=$this->getCustomerById($customerId);
		if($customer->getDefaultShipping() == $address->getId()){
		    $isDefault=true;
		}
	}
        return $isDefault;
    }

    /**
     * Create new customer address.
     * @return array
     */
    public function createAddress(){

        $post = file_get_contents("php://input");
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$api_auth = $objectManager->create('\Yalla\Apis\Helper\APIAuth');

		$auth = $api_auth->Auth(); 

		if($auth['success'] == "false"){
			echo json_encode($auth);
			exit;
		}
        if (!empty($post)) {
            $request = json_decode($post, true);
        }

	$response = ['status' => 'success', 'msg' => __('success'), 'collection' => array()];

	if(!isset($request['customer_id']) || empty($request['customer_id'])){
		$response['status'] = "success";
		$response['msg'] = __("Invalid customer.");
                $response['collection'] = [];
		echo json_encode($response);
		exit;
	}

	$customer_id = (int)$request['customer_id'];
		$alias = $request['alias'];
	$prefix = $request['prefix'];
        $firstname = $request['firstname'];
        $lastname = $request['lastname'];
        $country_id = $request['country_id'];
        $region_id = $request['region_id'];
        $region = $request['region'];
        $city = $request['city'];
        $street = $request['street'];
        $address_line_1 = $request['building_number'];
        $address_line_2 = $request['address_line_2'];
        $telephone = $request['telephone'];
        $postcode = $request['postcode'];
        $company = $request['company'];
        $building_number = $request['building_number'];
        $zone = $request['zone'];
        $landmark = $request['landmark'];
        $is_default_billing = $request['is_default_billing'];
        $is_default_shipping = $request['is_default_shipping'];

		if(!empty($request['zone'])){
        	$address_line_2 = $request['zone'];
		    if(!empty($request['landmark'])){
		    	$address_line_2.= ",".$request['landmark'];
		 	}    
       	}else{
      		$address_line_2 = $request['landmark'];
  		}
        $message = array();

	

	//Multi store view
        $lang = $this->request->getParam('lang');
		$country = $this->request->getParam('country');
		$apiHelper = $objectManager->get('Yalla\Apis\Helper\Data');
        $apiHelper->setStore($lang, $country);
        //Multi store view

        $customer=$this->getCustomerById($customer_id);
        if($customer->getId()){

            if(empty($street) && empty($address_line_1) && empty($address_line_2)){
                $streetData = array(
                    0 => $city
                );
            } else {
                $streetData = array(
                    0 => $street,
                    1 => $address_line_1,
                    2 => $address_line_2
                );
            }

            //set Address Data
            $address= $this->address->setCustomerId($customer_id)
                ->setPrefix($prefix)
                ->setAlias($alias)
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setCountryId($country_id)
                ->setRegionId($region_id)
                ->setRegion($region)
                ->setCity($city)
                ->setStreet($streetData)
                ->setTelephone($telephone)
                ->setPostcode($postcode)
                ->setCompany($company)
                //->setBuildingNumber($building_number) 
                ->setZone($zone)
                ->setLandmark($landmark)
                ->setIsDefaultBilling($is_default_billing)
                ->setIsDefaultShipping($is_default_shipping);

            try{
                $address->save();
                $addressId=$address->getId();
                if($addressId){
                    $response['status'] = 'success';
                    $response['msg']=__("Customer address created successfully");
                    $response['collection']=$this->getCustomerAddresses($customer_id);
                }
            }
            catch (\Exception $e) {
                $response['status'] = 'error';
                $response['msg'] = $e->getMessage();
            }
        }else{
            $response['status'] = 'error';
            $response['msg'] = __('Invalid Customer ID');
        }

	echo json_encode($response);
	exit;
    }


    /**
     * Update customer address.
     * @return array
     */
    public function updateAddress(){

	$post = file_get_contents("php://input");
        if (!empty($post)) {
            $request = json_decode($post, true);
        }

	$response = ['status' => 'success', 'msg' => __('success'), 'collection' => array()];

	if(!isset($request['customer_id']) || empty($request['customer_id'])){
		$response['status'] = "success";
		$response['msg'] = __("Invalid customer.");
                $response['collection'] = [];
		echo json_encode($response);
		exit;
	}

	if(!isset($request['address_id']) || empty($request['address_id'])){
		$response['status'] = "success";
		$response['msg'] = __("Invalid address.");
                $response['collection'] = [];
		echo json_encode($response);
		exit;
	}


		$id =  (int)$request['address_id'];
		$customer_id = (int)$request['customer_id'];
		$alias = $request['alias'];
		$prefix = $request['prefix'];
        $firstname = $request['firstname'];
        $lastname = $request['lastname'];
        $country_id = $request['country_id'];
        $region_id = $request['region_id'];
        $region = $request['region'];
        $city = $request['city'];
        $street = $request['street'];
        $address_line_1 = $request['building_number'];
        $address_line_2 = $request['address_line_2'];
        $telephone = $request['telephone'];
        $postcode = $request['postcode'];
        $company = $request['company'];
        $building_number = $request['building_number'];
        $zone = $request['zone'];
        $landmark = $request['landmark'];
        $is_default_billing = $request['is_default_billing'];
        $is_default_shipping = $request['is_default_shipping'];

		if(!empty($request['zone'])){
        	$address_line_2 = $request['zone'];
		    if(!empty($request['landmark'])){
		    	$address_line_2.= ",".$request['landmark'];
		 	}    
       	}else{
      		$address_line_2 = $request['landmark'];
  		}
  		
        $response = array();

        $address = $this->getCustomerAddressById($id);
        if($address->getId()){
            $customerId=$address->getParentId();
            if(empty($street) && empty($address_line_1) && empty($address_line_2)){
                $streetData = array(
                    0 => $city
                );
            } else {
                $streetData = array(
                    0 => $street,
                    1 => $address_line_1,
                    2 => $address_line_2
                );
            }

            //set Address Data
            $address->setPrefix($prefix)
            	->setAlias($alias)
		->setFirstname($firstname)
                ->setLastname($lastname)
                ->setCountryId($country_id)
                ->setRegionId($region_id)
                ->setRegion($region)
                ->setCity($city)
                ->setStreet($streetData)
                ->setTelephone($telephone)
                ->setPostcode($postcode)
                ->setCompany($company)
                //->setBuildingNumber($building_number)
                ->setZone($zone)
                ->setLandmark($landmark)
                ->setIsDefaultBilling($is_default_billing)
                ->setIsDefaultShipping($is_default_shipping);

            try{
                $address->save();
                $response['status'] = 'success';
                $response['msg']=__("Customer address updated successfully");
                $response['collection'] = $this->getCustomerAddresses($customerId);

            }
            catch (\Exception $e) {
                $response['status'] = 'error';
                $response['msg'] = $e->getMessage();
            }
        }else{
            $response['status'] = 'error';
            $response['msg'] = __('Invalid Address ID');
        }

	echo json_encode($response);
	exit;
    }

}
